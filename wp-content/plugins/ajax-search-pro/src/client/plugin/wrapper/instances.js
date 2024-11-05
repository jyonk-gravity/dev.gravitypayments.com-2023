import {default as $} from "domini";

window._asp_instances_storage = window._asp_instances_storage || [];

const instances = {
	instances: window._asp_instances_storage,

	get: function(id, instance) {
		this.clean();
		if ( typeof id === 'undefined' || id === 0) {
			return this.instances;
		} else {
			if ( typeof instance === 'undefined' ) {
				let ret = [];
				for ( let i=0; i<this.instances.length; i++ ) {
					if ( parseInt(this.instances[i].o.id) === id ) {
						ret.push(this.instances[i]);
					}
				}
				return ret.length > 0 ? ret : false;
			} else {
				for ( let i=0; i<this.instances.length; i++ ) {
					if ( parseInt(this.instances[i].o.id) === id && parseInt(this.instances[i].o.iid) === instance ) {
						return this.instances[i];
					}
				}
			}
		}
		return false;
	},
	set: function(obj) {
		if ( !this.exist(obj.o.id, obj.o.iid) ) {
			this.instances.push(obj);
			return true;
		} else {
			return false;
		}
	},
	exist: function(id, instance) {
		this.clean();
		for ( let i=0; i<this.instances.length; i++ ) {
			if ( parseInt(this.instances[i].o.id) === id ) {
				if (typeof instance === 'undefined') {
					return true;
				} else if (parseInt(this.instances[i].o.iid) === instance) {
					return true;
				}
			}
		}
		return false;
	},
	clean: function() {
		let unset = [], _this = this;
		this.instances.forEach(function(v, k){
			if ( $('.asp_m_' + v.o.rid).length === 0 ) {
				unset.push(k);
			}
		});
		unset.forEach(function(k){
			if ( typeof _this.instances[k] !== 'undefined' ) {
				_this.instances[k].destroy();
				_this.instances.splice(k, 1);
			}
		});
	},
	destroy: function(id, instance) {
		let i = this.get(id, instance);
		if ( i !== false ) {
			if ( Array.isArray(i) ) {
				i.forEach(function (s) {
					s.destroy();
				});
				this.instances = [];
			} else {
				let u = 0;
				this.instances.forEach(function(v, k){
					if ( parseInt(v.o.id) === id && parseInt(v.o.iid) === instance) {
						u = k;
					}
				});
				i.destroy();
				this.instances.splice(u, 1);
			}
		}
	}
};

export default instances;