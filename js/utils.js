var utils = {
	storage_field: "WPPAYMENT_LOCAL_DATA",
	storage_tmp: null,

	store: function(key, val){
		if(!utils.storage_tmp){
			utils.storage_tmp = JSON.parse(
				localStorage.getItem(utils.storage_field) 
				|| (localStorage.setItem(utils.storage_field, "{}"), "{}")
			);
		}
		if(val !== undefined){
			utils.storage_tmp = JSON.parse(localStorage.getItem(utils.storage_field));
			utils.storage_tmp[key] = val;
			localStorage.setItem(utils.storage_field, JSON.stringify(utils.storage_tmp));
		}else{
			return utils.storage_tmp[key];
		}
	},

	alert: function(title, msg, icon){
		Swal.fire({
			title: title,
			text: msg,
			icon: icon || 'warning',
			confirmButtonText: 'ok'
		})
	},

	redirect: function(url){
		window.location.href = url;
	},

	CommonVueMixin: {
		data: function(){
			return {
				showLoadingScreen: false,
				loadingScreenContent: ''
			};
		},
		methods: {
			
		}
	}
};