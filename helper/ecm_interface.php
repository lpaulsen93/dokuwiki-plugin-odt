<?php
/**
 * Definition of Interface iElementCSSMatchable
 * 
 * The goal of the interface is to define functions which
 * are required by the CSS import helper plugin (class 
 * helper_plugin_odt_cssimport, cssimport.php). To be more precise
 * these functions are required by the class css_selector to do the
 * matching with a given element.
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     LarsDW223
 */

/**
 * Interface iElementCSSMatchable
 *
 * To prevent clashes with other interfaces function names all functions
 * are prefixed with iECSSM_.
 * 
 * @package CSS\iElementCSSMatchable
 */
interface iElementCSSMatchable
{
    // Return the element's name as string.
    public function iECSSM_getName();

    // Return the element's attribute's as an array with
    // key-value pairs:
    // key = attribute-name,
    // value = attribute-value (without '"');
    public function iECSSM_getAttributes();

    // Return the element's parent.
    public function iECSSM_getParent();

    // Return the element's immediately preceding sibling
    public function iECSSM_getPrecedingSibling();

    // Does the element belong to the given pseudo class?
    // (e.g. 'visited', 'first-child')
    public function iECSSM_has_pseudo_class($class);

    // Does the element belong to the given pseudo element?
    // (e.g. 'first-letter', 'before')
    public function iECSSM_has_pseudo_element($element);
}
