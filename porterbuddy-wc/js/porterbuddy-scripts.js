/*
 * PorterBuddy WooCommerce plugin scripts
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

function showLoader ()
{
	jQuery( 'body' ).prepend('<span class="porterbuddy-loader"></span>');
}

function hideLoader ()
{
	jQuery( 'span' ).remove('.porterbuddy-loader');		
}