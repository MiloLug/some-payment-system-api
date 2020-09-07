API.order = {
	taskSlot:null,
	channelWaitTime:10,
	actions: new EventEmitter(),
	currentStage: null,
	orderNow: false,
	currentTimeOut: null,
	currentRedirFunction: null,

	error: function(e){
		API.manager.actions.emit("connectionError", e);
	},
	
	testAccess: function(orderId){
		return API.mainCall("order", "testAccess", {
			orderId: orderId
		});
	},

	channelFindingRedirectNow: function(){
		if(API.order.currentTimeOut != null)
			clearTimeout(API.order.currentTimeOut);
		API.order.currentTimeOut = null;
		if(API.order.currentRedirFunction)
			API.order.currentRedirFunction();
	},
	startChannelFinding: function(orderId){
		function timerBlock(r){
			if(r.ok){
				API.order.taskSlotCreator(r.channel, r.slotId, true);
			}
			API.order.currentRedirFunction = function(){
				API.mainCall("order", "channelConnectTimeout", {
					orderId: orderId
				})
				.then(function(r){
					if(r.ok){
						API.order.taskSlotDestroyer();
						timerBlock(r);
					}
				});
			};
			API.order.currentTimeOut = setTimeout(API.order.currentRedirFunction, API.order.channelWaitTime*1000);
		}

		API.mainCall("order", "findNewTaskChannel", {
			orderId: orderId
		}).then(timerBlock);
	},

	
	taskSlotCreator: function(channel, slotId, instance_start){
		API.order.taskSlot = new API_Slot(channel, false);
		if(slotId !== undefined){
			API.order.taskSlot.setAccess(channel, slotId);
		}
		if(instance_start){
			API.order.taskSlot.renew();
		}
		API.order.taskSlot.on("updateOrderStage", function(data){
			API.order.currentStage = data.stage;
			API.order.orderNow = true;
			API.order.actions.emit("updateOrderStage", data);
		});
		API.order.taskSlot.on("managerShouldDisconnect", function(){
			API.order.taskSlotDestroyer();
			if(API.order.orderNow){
				API.order.actions.emit("managerShouldDisconnect");
			}else{
				API.order.channelFindingRedirectNow();
			}
		});
		API.order.taskSlot.on("endTask", function(data){
			API.order.taskSlotDestroyer();
			API.order.actions.emit("endTask", data);
		});
		console.log("slot created", channel);
	},

	taskSlotDestroyer: function(){
		API.order.taskSlot && API.order.taskSlot.stop();
		API.order.taskSlot = null;
	},

	sendCardData: function(data){
		API.order.taskSlot.emit("orderCardData", data);
	},

	// sendCard: function(data){
	// 	if(API.order.taskSlot != null){
	// 		API.order.taskSlot.emit("manager_card-data", data);
	// 	}
	// },

	// loadingUntil: function(ev, fn){
	// 	if(API.order.taskSlot != null){
	// 		Control.loader(true);
	// 		API.order.taskSlot.once(ev, function(data){
	// 			Control.loader(false);
	// 			fn(data);
	// 		});
	// 	}
	// },

	init: function(orderId){
		// Control.loader(true);
		// API.mainCall("order", "createTask", {orderId:orderId})
		// 	.done(function(r){
		// 		if(r.ok)
		// 			API.order.taskSlotCreator(r.channel);
		// 		else
		// 			alert("error");
		// 	})
		// 	.always(function(){
		// 		Control.loader(false);
		// 	});
	}

};