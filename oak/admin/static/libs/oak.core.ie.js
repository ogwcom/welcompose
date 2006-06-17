/**
 * Project: Oak
 * File: oak.core.ie.js
 *
 * Copyright (c) 2004-2005 sopic GmbH
 *
 * Project owner:
 * sopic GmbH
 * 8472 Seuzach, Switzerland
 * http://www.sopic.com/
 *
 * This file is licensed under the terms of the Open Software License
 * http://www.opensource.org/licenses/osl-2.1.php
 *
 * $Id$
 *
 * @copyright 2006 creatics media.systems
 * @author Olaf Gleba
 * @package Oak
 * @license http://www.opensource.org/licenses/osl-2.1.php Open Software License
 */
 


function setCorrespondingFocus (elem, attr)
{
	instId = elem.parentNode.parentNode.attributes[attr].value; // attributes[attr].value
	$(instId).focus();
}

/**
 * Get help IDs and print string in html templates
 * used : oak.events.js
 *
 * @param {string} elem actual element
 * @param {string} attr attribute of DOM node to process (e.g. ID)
 */
function getHelp (elem, attr)
{	
	processId = elem.parentNode.parentNode.attributes[attr].value; // attributes[attr].value
	elem.className = helpClassRemove;
	Element.update(elem, helpHtmlHide);
	Behaviour.apply();	
}

/**
 * Get help IDs and print string in html templates
 * used : oak.events.js
 *
 * @param {string} elem actual element
 * @param {string} attr attribute of DOM node to process (e.g. ID)
 */
function removeHelp (elem, attr)
{	
	processIdRemove = elem.parentNode.parentNode.attributes[attr].value; // attributes[attr].value
	processId_after = $(processIdRemove).parentNode.nextSibling;	
	elem.className = helpClass;
	Element.hide(processId_after);
	Element.update(elem, helpHtmlShow);
	Behaviour.apply();
}