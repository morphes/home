/**
 * @author Vladimir Kolesnikov <voloko@gmail.com>
 * @copyright (c) Vladimir Kolesnikov <voloko@gmail.com>
 */
var lib = {
	version: '1.1.7',
	rootUrl: '/js-new/',
	context: this,
	versioninig: false
}

/**
 * From prototype.js. Can't live without them
 */
function f(element) {
	if (arguments.length > 1) {
		for (var i = 0, elements = [], length = arguments.length; i < length; i++)
			elements.push(f(arguments[i]));
		return elements;
	}
	if (typeof element == 'string')
		element = document.getElementById(element);
	return element;
}

/*

// Закомментили в связи с конфликтом API Яндекс карт

Function.prototype.bind = function() {
	var __method = this;
	var object = arguments[0];
	return function() {
		return __method.apply(object);
	}
}*/

Object.extend = function(destination, source) {
	for (var property in source) {
		destination[property] = source[property];
	}
	return destination;
}

lib.getXHTTPTransport = function() {
	var result = false;
	var actions = [
		function() {return new XMLHttpRequest()},
		function() {return new ActiveXObject('Msxml2.XMLHTTP')},
		function() {return new ActiveXObject('Microsoft.XMLHTTP')}
	];
	for(var i = 0; i < actions.length; i++) {
		try {
			result = actions[i]();
			break;
		} catch (e) {}
	}
	return result;
}

/**
 * @param {Object} object
 * @param {String} name
 * @param {Object?} value
 * @param {bool?} forceSet
 */
lib.evalProperty = function(object, name, value, forceSet) {
	if(object) {
		if(!object[name] || forceSet) object[name] = value || true;
		return object[name];
	}
	return null;
}
/**
 * @param {String} path
 * @param {Object?} context
 * @param {Object?} value
 * @param {bool?} forceSet
 */
lib.evalPath = function(path, context, value, forceSet) {
	context = context || lib.context;
	var pos = path.indexOf('.');
	if(pos == -1) {
		return lib.evalProperty(context, path, value, forceSet);
	} else {
		var name = path.substring(0, pos);
		var path = path.substring(pos + 1);
		var obj = lib.evalProperty(context, name, value);
		return lib.evalPath(path, obj, value, forceSet);
	}
}

/**
 * @param {String} path
 * @param {float} version
 * @return {String}
 */
lib.pathToUrl = function(path, version) {
	return lib.rootUrl + path.replace(/\./g, '/') +
		(lib.versioninig ? '.v' + version : '') + '.js';
}
/**
 * @type {Array}
 */
lib.loadedModules = {};

/**
 * @param {String} path
 * @param {float} version
 */
lib.module = function(path, version) {
	version = version || 1.0;
	lib.loadedModules[path] = lib.loadedModules[path] ? Math.max(lib.loadedModules[path], version) : version;
	return lib.evalPath(path, null, {});
}


/**
 * @param {String} path
 * @param {float} version
 */
lib.include = function(path, version) {
	version = version || 1.0;
	if(lib.loadedModules[path] && lib.loadedModules[path] >= version) return false;
	var transport = lib.getXHTTPTransport();
	transport.open('GET', lib.pathToUrl(path, version), false);
	transport.send(null);

	var code = transport.responseText;
	(typeof execScript != 'undefined') ? execScript(code) :
		(lib.context.eval ? lib.context.eval(code) : eval(code));

	//console.log(lib.pathToUrl(path, version));

	return true;
}
lib.load = lib.include;

/**
 * @param {String} newClass
 * @param {Object} superClass
 * @param {Object} props
 */
lib.extend = function(newClass, superClass, props) {
	var multiple = [];
	if(superClass instanceof Array || typeof superClass == 'array') {
		multiple = superClass;
		superClass = multiple.shift();
	}
	if(typeof newClass == 'string') {
		newClass = lib.evalPath(newClass, null, lib.createClass(), 1);
	} else {
		return;
	}

	if(superClass) {
		var inheritance = function() {};
		inheritance.prototype = superClass.prototype;

		newClass.prototype = new inheritance();
		newClass.superClass = superClass.prototype;
	}
	for(var i = 0; i < multiple.length; i++) {
		Object.extend(newClass.prototype, multiple[i].prototype);
	}
	newClass.mixins = multiple;

	Object.extend(newClass.prototype, props || {});

	newClass.prototype.constructor = newClass;
}
lib.define = lib.extend;

lib.createClass = function() {
    return function() {
		var _this = arguments.callee.prototype;
		_this.init.apply(this, arguments);
		for(var i = 0, mixins = _this.constructor.mixins, length = mixins.length; i < length; i++){
			mixins[i].init.apply(this);
		}
    }
}

lib.hasOwnProperty = function(obj, prop) {
        if (Object.prototype.hasOwnProperty) {
            return obj.hasOwnProperty(prop);
        }

        return typeof obj[prop] != 'undefined' &&
                obj.constructor.prototype[prop] !== obj[prop];
}

lib.dump = function(text){};
lib.error = function(text){};

restorejs = function(obj) {
	return function() {
		window.js = obj;
	}
}(lib);