API.manager = {
	taskSlot:null,
	orders:[],
	order_exists:{},
	mdata: null,
	currentOrder: null,

	actions: new EventEmitter(),

	error: function(e){
		API.manager.actions.emit("serverError", e);
	},
	
	getManagerByAccesses: function(login, pass){
		API.mainCall("manager", "getManagerByAccesses", {
			login: login,
			pass: pass
		}).then(function(r){
			if(r.ok){
				API.manager.init(r);
				API.manager.actions.emit("managerHasLoggedIn", r);
			}else{
				API.manager.actions.emit("managerAccessesAreIncorrect");
			}
		})
		.catch(API.manager.error);
	},

	getManagerByUID: function(managerId, managerUID){
		API.mainCall("manager", "getManagerByUID", {
			managerId: managerId,
			managerUID: managerUID
		})
		.then(function(r){
			if(r.ok){
				var mgr = {
					name: r.name,
					id: managerId,
					uid: managerUID
				};
				API.manager.init(mgr);
				API.manager.actions.emit("managerHasLoggedIn", mgr);
			}else{
				API.manager.actions.emit("managerAccessesAreIncorrect");
			}
		})
		.catch(API.manager.error);
	},

	removeLocalOrderById: function(id){
		for(var i = 0, order; order = API.manager.orders[i]; i++){
			if(order.orderData.id == id){
				API.manager.orders.splice(i, 1);
				delete API.manager.order_exists[order.orderData.id];
			}
		}
	},
	
	taskSlotCreator: function(channel, slotId, instance_start){
		API.manager.taskSlot = new API_Slot(channel, false);
		if(slotId !== undefined){
			API.manager.taskSlot.setAccess(channel, slotId);
		}
		if(instance_start){
			API.manager.taskSlot.renew();
		}
		API.manager.taskSlot.on("orderCardData", function(data){
			API.manager.currentOrder.orderData.card = data;
			API.manager.actions.emit("updateCurrentOrder", API.manager.currentOrder);
		});
		API.manager.taskSlot.on("endTask", function(data){
			console.log(data,21313312);
			if(API.manager.currentOrder != null && API.manager.currentOrder.orderData.id == data.orderId){
				API.manager.taskSlotDestroyer();
				API.manager.actions.emit("endTask");
				API.manager.currentOrder = null;
			}
		});
		console.log("slot created", channel);
	},

	taskSlotDestroyer: function(){
		API.manager.taskSlot && API.manager.taskSlot.stop();
		API.manager.taskSlot = null;
	},

	online: function(){
		API.mainCall("manager", "activate", {
			managerId: API.manager.mdata.id,
			managerUID: API.manager.mdata.uid
		})
		.then(function(r){
			if(r.ok){
				API.defaultSlot.setAccess(API.manager.mdata.uid, r.slotId);
				API.defaultSlot.renew();
				API.manager.actions.emit("changeOnline", true);
			}else{
				API.manager.actions.emit("changeOnlineSessionError", true);
			}
		})
		.catch(API.manager.error);
	},

	offline: function(){
		API.defaultSlot.stop();
		API.mainCall("manager", "deactivate", {
			managerUID: API.manager.mdata.uid,
			managerId: API.manager.mdata.id
		})
		.then(function(r){
			API.manager.actions.emit("changeOnline", false);
		})
		.catch(API.manager.error);
	},

	processTheOrder: function(i){
		var obj = API.manager.orders[i];
		if(obj){
			API.mainCall("order", "takeOrder", {
				orderId: obj.orderData.id,
				managerUID: API.manager.mdata.uid
			})
			.then(function(r){
				API.manager.orders.splice(i, 1);
				delete API.manager.order_exists[obj.orderData.id];
				
				if(r.ok){
					API.manager.currentOrder = obj;
					API.manager.taskSlotCreator(r.channel, r.slotId, true);
					API.manager.actions.emit("startOrder");
				}else{
					API.manager.actions.emit("startOrderError");
				}
			});
			return true;
		}
		return false;
	},

	acceptOrder: function(){
		if(API.manager.currentOrder){
			API.mainCall("order","makePayed", {
				orderId: API.manager.currentOrder.orderData.id,
				managerUID: API.manager.mdata.uid
			});
		}
	},

	declineOrder: function(){
		if(API.manager.currentOrder){
			API.mainCall("order","makeDeclined", {
				orderId: API.manager.currentOrder.orderData.id,
				managerUID: API.manager.mdata.uid
			});
		}
	},

	setClientOrderState: function(stage){
		API.manager.taskSlot.emit("updateOrderStage",{
			stage: stage
		});
	},

	init: function(mgr){
		if(API.manager.taskSlot){
			API.manager.taskSlotDestroyer();
			API.defaultSlot.stop();
		}

		API.manager.mdata = mgr;
		API.init(mgr.uid, false);

		API.defaultSlot.on("manager_addOrder", function(r){
			if(!API.manager.order_exists[r.orderData.id]){
				API.manager.order_exists[r.orderData.id] = true;
				API.manager.orders.push(r);
				API.manager.actions.emit("updateOrders", API.manager.orders);
			}
		});

		API.defaultSlot.on("orderShouldDisconnect", function(data){
			console.log(data);
			if(API.manager.currentOrder != null && API.manager.currentOrder.orderData.id == data.orderId){
				API.manager.taskSlotDestroyer();
				API.manager.actions.emit("orderShouldDisconnect");
			}else{
				API.manager.removeLocalOrderById(data.orderId);
				API.manager.actions.emit("updateOrders", API.manager.orders);
			}
		});
	}

};