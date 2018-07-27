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
 * General loading icon
 */
function showLoader ( element )
{
	if ( element != undefined )
	{
		jQuery( element ).prepend('<span class="porterbuddy-loader"></span>');
	}
	else 
	{
		jQuery( 'body' ).prepend('<span class="porterbuddy-loader"></span>');
	}
	
}
function hideLoader ( element )
{

	if ( element != undefined )
	{
		jQuery( element ).find('span').remove('.porterbuddy-loader');
	}
	else 
	{
		jQuery( 'span' ).remove('.porterbuddy-loader');		
	}
	
}

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
