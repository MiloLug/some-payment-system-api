/**
 * Heir v4.0.0 - http://git.io/F87mKg
 * Oliver Caldwell - https://oli.me.uk/
 * Unlicense - http://unlicense.org/
 */

var heir = {
	inherit: function inherit(destination, source, addSuper) {
		var proto = destination.prototype = Object.create(source.prototype);
		proto.constructor = destination;

		if (addSuper || typeof addSuper === 'undefined') {
			destination.prototype._super = source.prototype;
		}
	},

	mixin: function mixin(destination, source) {
		var key;

		for (key in source) {
			if (source.hasOwnProperty(key)) {
				destination.prototype[key] = source[key]
			}
		}
	}
}
