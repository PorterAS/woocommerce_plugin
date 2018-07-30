/*
 * PorterBuddy WooCommerce plugin scripts
 */

/**
 * JS cookie functions
 */
function PBsetCookie (cname, cvalue, expmin) {
	
	var d = new Date();
	d.setTime(d.getTime() + (expmin*60*1000));
	var expires = "expires="+ d.toUTCString();
	document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
}

function PBgetCookie (name) {
  
  var value = "; " + document.cookie;
  var parts = value.split("; " + name + "=");
  
  if (parts.length == 2) return parts.pop().split(";").shift();
}


/**
 * Check if a node is blocked for processing.
 *
 * @param {JQuery Object} $node
 * @return {bool} True if the DOM Element is UI Blocked, false if not.
 */
var is_blocked = function( $node ) {
	return jQuery($node).is( '.processing' ) || jQuery($node).parents( '.processing' ).length;
};

/**
 * Block a node visually for processing.
 *
 * @param {JQuery Object} $node
 */
var block = function( $node ) {
	if ( ! is_blocked( $node ) ) {
		jQuery($node).addClass( 'processing' ).block( {
			message: null,
			overlayCSS: {
				background: '#fff',
				opacity: 0.6
			}
		} );
	}
};

/**
 * Unblock a node after processing is complete.
 *
 * @param {JQuery Object} $node
 */
var unblock = function( $node ) {
	jQuery($node).removeClass( 'processing' ).unblock();
};


/** 
 * Continously check if elements are ready
 *
ready('element-identifier'), function(element) {
// function to be executed
}
 *
 */
(function(win) {
    'use strict';
    
    var listeners = [], 
    doc = win.document, 
    MutationObserver = win.MutationObserver || win.WebKitMutationObserver,
    observer;
    
    function ready(selector, fn) {
        // Store the selector and callback to be monitored
        listeners.push({
            selector: selector,
            fn: fn
        });
        if (!observer) {
            // Watch for changes in the document
            observer = new MutationObserver(check);
            observer.observe(doc.documentElement, {
                childList: true,
                subtree: true
            });
        }
        // Check if the element is currently in the DOM
        check();
    }
        
    function check() {
        // Check the DOM for elements matching a stored selector
        for (var i = 0, len = listeners.length, listener, elements; i < len; i++) {
            listener = listeners[i];
            // Query for elements matching the specified selector
            elements = doc.querySelectorAll(listener.selector);
            for (var j = 0, jLen = elements.length, element; j < jLen; j++) {
                element = elements[j];
                // Make sure the callback isn't invoked with the 
                // same element more than once
                if (!element.ready) {
                    element.ready = true;
                    // Invoke the callback with the element
                    listener.fn.call(element, element);
                }
            }
        }
    }

    // Expose `ready`
    win.ready = ready;
            
})(this);


