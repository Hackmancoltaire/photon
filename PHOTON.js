function escapeHTML(input) {
	text = input.replace(/'/g,'&#039;');
	text = text.replace(/\"/g,'&#034;');
	//text = text.replace(/</g,'&#060;');
	//text = text.replace(/>/g,'&#062;');
	return text.replace(/\n/g,'<br>');
}

function unescapeHTML(input) {
	text = input.replace(/&#039;/g,"'");
	text = text.replace(/&#034;/g,'"');
	//text = text.replace(/&#060;/g,'<');
	//text = text.replace(/&#062;/g,'>');
	return text.replace(/<br>/g,'\n');
}


function htmlEntities(texto){
    //by Micox - elmicoxcodes.blogspot.com - www.ievolutionweb.com
    var i,carac,letra,novo='';
    for(i=0;i<texto.length;i++){
        carac = texto[i].charCodeAt(0);
        if((carac < 33) || (carac > 39 && carac < 126)){
            //se for numero ou letra normal
            novo += texto[i];
        }else{
            novo += "&#" + texto[i].charCodeAt(0) + ";";
        }
    }
    return novo;
}

function subWindow(subWidth,subHeight,subLocation,subName) {
	subName=subName.replace(/ /,'_'); // Added this because IE dosent allow spaces in window names. Yet ALL the other browsers do.
	newWindow = window.open(subLocation,subName,'width=' + subWidth + ',height=' + subHeight + ',menubar=yes,scrollbars=yes,resizable=yes');
	newWindow.focus();
}

/* PHOTON AJAX */

function phSubmitForm(formName,method,action,callback) {
	if (method == "standard"|null) {
		// Standard method. Submit form regularly
		document.forms[formName].elements['action'].value = action;
		document.forms[formName].submit();
	}
	else if (method == "phAJAX") {
		var phAJAXOutbox = "method=phAJAX&action=" + escape(action) + "&";
		
		elementCount = document.forms[formName].elements.length;

		for (i=0; i < elementCount; i++) {
			if (document.forms[formName].elements[i].name.indexOf("_phIgnore") == -1) {
				phAJAXOutbox += (i > 0 ? "&" : "") + encodeURIComponent(document.forms[formName].elements[i].name) + "=" + encodeURIComponent(document.forms[formName].elements[i].value);
			}
		}
		
		var req = new XMLHttpRequest();
		req.open('POST', document.forms[formName].action, true);
		req.setRequestHeader("Content-Type","application/x-www-form-urlencoded; charset=UTF-8");

		req.onreadystatechange = function (aEvt) {
			if (req.readyState == 4) {
				if(req.status == 200) { callback(formName,true,req.responseText); }
				else { callback(formName,false,req.responseText); }
			}
		};
		
		req.send(phAJAXOutbox);
	}
	else if (method == "AJAX") {
		var phAJAXOutbox = "";
		var req = new XMLHttpRequest();
		
		if (document.forms[formName].method.toLowerCase() == "post" || document.forms[formName].method == null) {
			req.open('POST', document.forms[formName].action, true);
			req.setRequestHeader("Content-Type","application/x-www-form-urlencoded; charset=UTF-8");

			elementCount = document.forms[formName].elements.length;

			for (i=0; i < elementCount; i++) {
				if (document.forms[formName].elements[i].name.indexOf("_phIgnore") == -1) {
					phAJAXOutbox += (i > 0 ? "&" : "") + encodeURIComponent(document.forms[formName].elements[i].name) + "=" + encodeURIComponent(document.forms[formName].elements[i].value);
				}
			}

			req.onreadystatechange = function (aEvt) {
				if (req.readyState == 4) {
					if(req.status == 200) { callback(formName,true,req.responseText); }
					else { callback(formName,false,req.responseText); }
				}
			};
			
			req.send(phAJAXOutbox);
		}
		else if (document.forms[formName].method.toLowerCase() == "get") {
			elementCount = document.forms[formName].elements.length;

			if (document.forms[formName].elements["phLocalXML_phIgnore"].value == 0) {
				// This means we'll be using the XML.php file to get the results from a source other than locally. Ths means we need to encode the & signs in the URL
				encodeAnds = true;
			}
			else { encodeAnds = false; }

			notFirst = false;

			for (i=0; i < elementCount; i++) {
				if (document.forms[formName].elements[i].name.indexOf("_phIgnore") == -1) {
					phAJAXOutbox += ((i > 0 && notFirst) ? (encodeAnds ? encodeURIComponent("&"):"&") : "") + encodeURIComponent(document.forms[formName].elements[i].name) + "=" + encodeURIComponent(document.forms[formName].elements[i].value);
					notFirst = true;
				}
			}
			
			//alert(document.forms[formName].action + "?" + phAJAXOutbox);
			
			req.open('GET', document.forms[formName].action + "?" + phAJAXOutbox, true);
			req.setRequestHeader("Content-Type","application/x-www-form-urlencoded; charset=UTF-8");

			req.onreadystatechange = function (aEvt) {
				if (req.readyState == 4) {
					if(req.status == 200) { callback(formName,true,req.responseText); }
					else { callback(formName,false,req.responseText); }
				}
			};
			
			req.send();
		}
	}
}

function phLoadDocument(url,callback,layer,show) {
	var req = new XMLHttpRequest();
	req.open('GET', url, true);
	//req.setRequestHeader("Content-Type","application/x-www-form-urlencoded; charset=UTF-8");

	req.onreadystatechange = function (aEvt) {
		if (req.readyState == 4) {
			if(req.status == 200) { callback(req.responseText,layer,show); }
			else { callback(req.responseText,layer,show); }
		}
	};
	
	req.send(url);
}

// PHOTON SWITCHER CODE

function phSwitcherActive(switcher) {
	if (switcher.getAttribute("class") != "phScope_clicked") { switcher.setAttribute("class","phScope_active"); }
}

function phSwitcherInactive(switcher) {
	if (switcher.getAttribute("class") != "phScope_clicked") { switcher.setAttribute("class","phScope_inActive"); }
}

function phSwitcherClick(switcher) {
	switcherItems = document.getElementsByName(switcher.getAttribute("name"));
	
	idCount = switcherItems.length;
			
	for(i=0; i < idCount; i++) {
		if (switcherItems.item(i) == switcher) { scope.setAttribute("class","phScope_clicked"); }
		else { switcherItems.item(i).setAttribute("class","phScope_inActive"); }
	}
}

/*

uuid.js - Version 0.1
JavaScript Class to create a UUID like identifier

Copyright (C) 2006, Erik Giberti (AF-Design), All rights reserved.

This program is free software; you can redistribute it and/or modify it under 
the terms of the GNU General Public License as published by the Free Software 
Foundation; either version 2 of the License, or (at your option) any later 
version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY 
WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A 
PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with 
this program; if not, write to the Free Software Foundation, Inc., 59 Temple 
Place, Suite 330, Boston, MA 02111-1307 USA

The latest version of this file can be downloaded from
http://www.af-design.com/resources/javascript_uuid.php

HISTORY:
6/5/06 - Initial Release

*/


// on creation of a UUID object, set it's initial value
function UUID(){
	this.id = this.createUUID();
}



// When asked what this Object is, lie and return it's value
UUID.prototype.valueOf = function(){ return this.id; }
UUID.prototype.toString = function(){ return this.id; }



//
// INSTANCE SPECIFIC METHODS
//



UUID.prototype.createUUID = function(){
	// JavaScript Version of UUID implementation.
	//
	// Copyright 2006 Erik Giberti, all rights reserved.
	//
	// Loose interpretation of the specification DCE 1.1: Remote Procedure Call
	// described at http://www.opengroup.org/onlinepubs/009629399/apdxa.htm#tagtcjh_37
	// since JavaScript doesn't allow access to internal systems, the last 48 bits 
	// of the node section is made up using a series of random numbers (6 octets long).
	//  
	var dg = UUID.timeInMs(new Date(1582, 10, 15, 0, 0, 0, 0));
	var dc = UUID.timeInMs(new Date());
	var t = dc - dg;
	var h = '-';
	var tl = UUID.getIntegerBits(t,0,31);
	var tm = UUID.getIntegerBits(t,32,47);
	var thv = UUID.getIntegerBits(t,48,59) + '1'; // version 1, security version is 2
	var csar = UUID.getIntegerBits(UUID.randrange(0,4095),0,7);
	var csl = UUID.getIntegerBits(UUID.randrange(0,4095),0,7);

	// since detection of anything about the machine/browser is far to buggy, 
	// include some more random numbers here
	// if nic or at least an IP can be obtained reliably, that should be put in
	// here instead.
	var n = UUID.getIntegerBits(UUID.randrange(0,8191),0,7) + 
			UUID.getIntegerBits(UUID.randrange(0,8191),8,15) + 
			UUID.getIntegerBits(UUID.randrange(0,8191),0,7) + 
			UUID.getIntegerBits(UUID.randrange(0,8191),8,15) + 
			UUID.getIntegerBits(UUID.randrange(0,8191),0,15); // this last number is two octets long
	return tl + h + tm + h + thv + h + csar + csl + h + n; 
}



//
// GENERAL METHODS (Not instance specific)
//



// Pull out only certain bits from a very large integer, used to get the time
// code information for the first part of a UUID. Will return zero's if there 
// aren't enough bits to shift where it needs to.
UUID.getIntegerBits = function(val,start,end){
	var base16 = UUID.returnBase(val,16);
	var quadArray = new Array();
	var quadString = '';
	var i = 0;
	for(i=0;i<base16.length;i++){
		quadArray.push(base16.substring(i,i+1));	
	}
	for(i=Math.floor(start/4);i<=Math.floor(end/4);i++){
		if(!quadArray[i] || quadArray[i] == '') quadString += '0';
		else quadString += quadArray[i];
	}
	return quadString;
}

// Numeric Base Conversion algorithm from irt.org
// In base 16: 0=0, 5=5, 10=A, 15=F
UUID.returnBase = function(number, base){
	//
	// Copyright 1996-2006 irt.org, All Rights Reserved.	
	//
	// Downloaded from: http://www.irt.org/script/146.htm	
	// modified to work in this class by Erik Giberti
	var convert = ['0','1','2','3','4','5','6','7','8','9','A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z'];
    if (number < base) var output = convert[number];
    else {
        var MSD = '' + Math.floor(number / base);
        var LSD = number - MSD*base;
        if (MSD >= base) var output = this.returnBase(MSD,base) + convert[LSD];
        else var output = convert[MSD] + convert[LSD];
    }
    return output;
}

// This is approximate but should get the job done for general use.
// It gets an approximation of the provided date in milliseconds. WARNING:
// some implementations of JavaScript will choke with these large numbers
// and so the absolute value is used to avoid issues where the implementation
// begin's at the negative value.
UUID.timeInMs = function(d){
	var ms_per_second = 100; // constant
	var ms_per_minute = 6000; // ms_per second * 60;
	var ms_per_hour   = 360000; // ms_per_minute * 60;
	var ms_per_day    = 8640000; // ms_per_hour * 24;
	var ms_per_month  = 207360000; // ms_per_day * 30;
	var ms_per_year   = 75686400000; // ms_per_day * 365;
	return Math.abs((d.getUTCFullYear() * ms_per_year) + (d.getUTCMonth() * ms_per_month) + (d.getUTCDate() * ms_per_day) + (d.getUTCHours() * ms_per_hour) + (d.getUTCMinutes() * ms_per_minute) + (d.getUTCSeconds() * ms_per_second) + d.getUTCMilliseconds());
}

// pick a random number within a range of numbers
// int c randrange(int a, int b); where a <= c <= b
UUID.randrange = function(min,max){
	var num = Math.round(Math.random() * max);
	if(num < min){ 
		num = min;
	} else if (num > max) {
		num = max;
	}
	return num;
}

// end of UUID class file

var isFunction = function(o) {
	return typeof(o) == 'function' && (!Function.prototype.call || typeof(o.call) == 'function');
};
