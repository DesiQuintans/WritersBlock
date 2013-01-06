// 'The Punctuator' v1.0
//
// Derived from JS QuickTags version 1.2, Copyright (c) 2002-2005 Alex King
// http://www.alexking.org/
// Heavily cut down by Desi Quintans, Copyright (c) 2005 Desi Quintans
// http://www.desiquintans.com/
//
// Licensed under the GPL
// See documentation/gpl.txt.
// This library is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.

var edButtons = new Array();
var edOpenTags = new Array();

function edButton(id, display, tagStart, tagEnd, access, open) {
	this.id = id;				// button ID
	this.display = display;		// button text
	this.tagStart = tagStart; 	// open tag
	this.tagEnd = tagEnd;		// close tag
	this.access = access;		// accesskey
	this.open = open;
}

edButtons.push(
	new edButton('Boldface','Bold','<strong>','</strong>','B')
);

edButtons.push(
	new edButton('Italicise','Italic','<em>','</em>','I')
);

edButtons.push(
   new edButton('Em Dash','M &#8212;','&#8212;','','M')
);

edButtons.push(
   new edButton('En Dash','N &#8211;','&#8211;','','N')
);

edButtons.push(
   new edButton('Single Quotes','&#8216; ... &#8217;','&#8216;','&#8217;','Q')
);

edButtons.push(
   new edButton('Curly Quotes','&#8220; ... &#8221;','&#8220;','&#8221;','D')
);

edButtons.push(
   new edButton('Ellipsis','&#8230;','&#8230;','','.')
);

edButtons.push(
   new edButton('One-Dot Leader','1-.','&#8228;','','L')
);

edButtons.push(
   new edButton('Apostrophe','&#8217;','&#8217;','','A')
);

edButtons.push(
   new edButton('Ampersand','&#38;','&#38;','','7')
);

edButtons.push(
   new edButton('Less Than/Greater Than','&#60; ... &#62;','&#60;','&#62;','H')
);

edButtons.push(
   new edButton('Paragraph','P','<p>','</p>','Z')
);

edButtons.push(
   new edButton('Linebreak','<br />','<br />','','X')
);

edButtons.push(
   new edButton('Book Desc. Block','Book Desc. Block','<p>\r<em></em>\r</p>\r\r<blockquote>\r&#8220;&#8221;\r</blockquote>\r<p align="center">***</p>','','K')
);

edButtons.push(
   new edButton('Blockquote','<blockquote>','<blockquote>\r&#8220;&#8221;\r</blockquote>\r<p align="center">***</p>','','C')
);

function edShowButton(button, i) {
		var accesskey = 'accesskey="' + button.access + '"'
		document.write('<input type="button" id="' + button.id + '" ' + accesskey + ' class="ed_button" title="' + button.id + ' [alt + ' + button.access + ']" onclick="edInsertTag(edCanvas, ' + i + ');" value="' + button.display + '" />');
}

function edAddTag(button) {
	if (edButtons[button].tagEnd != '') {
		edOpenTags[edOpenTags.length] = button;
		document.getElementById(edButtons[button].id).value = '/' + document.getElementById(edButtons[button].id).value;
	}
}

function edRemoveTag(button) {
	for (i = 0; i < edOpenTags.length; i++) {
		if (edOpenTags[i] == button) {
			edOpenTags.splice(i, 1);
			document.getElementById(edButtons[button].id).value = 		document.getElementById(edButtons[button].id).value.replace('/', '');
		}
	}
}

function edCheckOpenTags(button) {
	var tag = 0;
	for (i = 0; i < edOpenTags.length; i++) {
		if (edOpenTags[i] == button) {
			tag++;
		}
	}
	if (tag > 0) {
		return true; // tag found
	}
	else {
		return false; // tag not found
	}
}

function edToolbar() {
	document.write('<div id="ed_toolbar">');
	for (i = 0; i < edButtons.length; i++) {
		edShowButton(edButtons[i], i);
	}
	document.write('</div>');
}

// insertion code

function edInsertTag(myField, i) {
	//IE support
	if (document.selection) {
		myField.focus();
	    sel = document.selection.createRange();
		if (sel.text.length > 0) {
			sel.text = edButtons[i].tagStart + sel.text + edButtons[i].tagEnd;
		}
		else {
			if (!edCheckOpenTags(i) || edButtons[i].tagEnd == '') {
				sel.text = edButtons[i].tagStart;
				edAddTag(i);
			}
			else {
				sel.text = edButtons[i].tagEnd;
				edRemoveTag(i);
			}
		}
		myField.focus();
	}
	//MOZILLA/NETSCAPE support
	else if (myField.selectionStart || myField.selectionStart == '0') {
		var startPos = myField.selectionStart;
		var endPos = myField.selectionEnd;
		var cursorPos = endPos;
		var scrollTop = myField.scrollTop;
		if (startPos != endPos) {
			myField.value = myField.value.substring(0, startPos)
			              + edButtons[i].tagStart
			              + myField.value.substring(startPos, endPos) 
			              + edButtons[i].tagEnd
			              + myField.value.substring(endPos, myField.value.length);
			cursorPos += edButtons[i].tagStart.length + edButtons[i].tagEnd.length;
		}
		else {
			if (!edCheckOpenTags(i) || edButtons[i].tagEnd == '') {
				myField.value = myField.value.substring(0, startPos) 
				              + edButtons[i].tagStart
				              + myField.value.substring(endPos, myField.value.length);
				edAddTag(i);
				cursorPos = startPos + edButtons[i].tagStart.length;
			}
			else {
				myField.value = myField.value.substring(0, startPos) 
				              + edButtons[i].tagEnd
				              + myField.value.substring(endPos, myField.value.length);
				edRemoveTag(i);
				cursorPos = startPos + edButtons[i].tagEnd.length;
			}
		}
		myField.focus();
		myField.selectionStart = cursorPos;
		myField.selectionEnd = cursorPos;
		myField.scrollTop = scrollTop;
	}
	else {
		if (!edCheckOpenTags(i) || edButtons[i].tagEnd == '') {
			myField.value += edButtons[i].tagStart;
			edAddTag(i);
		}
		else {
			myField.value += edButtons[i].tagEnd;
			edRemoveTag(i);
		}
		myField.focus();
	}
}