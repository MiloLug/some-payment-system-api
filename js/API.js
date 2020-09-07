var API;
function API_Slot(channel, instanceStart){
	var _this = this;
	var stop = true;

	_this.slotId = -1;
	_this.channel = channel;
	_this.currentRequest = null;
	_this.abortController = null;

	_this.setAccess = function(channel, slotId){
		_this.slotId = slotId;
		_this.channel = channel;
	}

	_this.emit = function(ev, data, slot, emitSelf){
		API.mainCall("event", "emit", {
			data: JSON.stringify(data),
			channel: _this.channel,
			slotId: slot||"",
			thisSlot: _this.slotId,
			emitSelf: !!emitSelf ? "1" : "",
			name: ev
		});
		return _this;
	};
	
	var startPolling = function(){
		if(stop)
			return;

		_this.abortController = new AbortController();
		_this.currentRequest = API.mainCall("event", "on", {
			slotId: _this.slotId+""
		}, _this.abortController.signal);

		_this.currentRequest
		.then(function(r){
			try{
				if(r.ok && r.events) r.events.forEach(function(ev){
					_this._super.emit.call(_this, ev.name, JSON.parse(ev.data||'""'));
				});
			}catch(e){
				console.error(e);
			}
		})
		.catch(function(r){
			if(r.name != "AbortError")
				console.error(r);
		})
		.finally(function(){
			startPolling();
		});
	};

	_this.stop = function(){
		stop = true;
		_this.slotId = -1;
		if(_this.abortController != null){
			_this.abortController.abort();
		}
	};
	_this.renew = function(){
		if(!stop)
			return;
		stop = false;
		if(_this.slotId == -1)
			API.mainCall("event", "createSlot", {channel:channel})
			.then(function(r){
				if(r.ok){
					_this.slotId = r.id;
					startPolling();
				}
			})
			.catch(console.error);
		else
			startPolling();
	};

	if(instanceStart){
		_this.renew();
	}
}
heir.inherit(API_Slot, EventEmitter);

API = {
	url:wp_ajax.url,
	defaultSlot: null,

	mainCall: function(space, method, args, signal){
		var data = {};
		
		var fd = new FormData();
		for(var key in args){
			fd.append("API_argument_"+key, args[key]);
		}

		fd.append("API_argument_space", space);
		fd.append("API_argument_method", method);
		fd.append("action", "API_main_call");

		return fetch(API.url, {
			method: "POST",
			body: fd,
			signal: signal
		}).then(function(r){
			return r.json();
		});
	},

	init: function(channel){
		API.defaultSlot = new API_Slot(channel);
	}
};

