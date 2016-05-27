<?php

require_once DOKU_INC.'lib/plugins/odt/helper/cssdocument.php';
require_once DOKU_INC.'lib/plugins/odt/helper/cssimport.php';

/**
 * Tests to ensure functionality of the (new) CSS import classes.
 *
 * @group plugin_odt
 * @group plugins
 */
class plugin_odt_cssimportnew_test extends DokuWikiTest {
    public function setUp() {
        $this->pluginsEnabled[] = 'odt';
        parent::setUp();
    }

    public static function setUpBeforeClass(){
        parent::setUpBeforeClass();

        // copy CSS test files to test directory
        TestUtils::rcopy(TMP_DIR, dirname(__FILE__) . '/data/');
    }

    /**
     * Ensure that the constructur sets the right properties and the getters
     * return them correctly.
     */
    public function test_simple_css_declaration() {
        $decl = new css_declaration ('color', 'black');

        $this->assertEquals($decl->getProperty(), 'color');
        $this->assertEquals($decl->getValue(), 'black');
    }

    /**
     * Ensure that the shorthand 'border' is exploded correctly.
     */
    public function test_border_shorthand() {
        /** @var css_declaration[] $decls */
        $decls = array();
        $decl = new css_declaration ('border', '5px solid red;');
        $decl->explode ($decls);

        $this->assertEquals(count($decls), 19);
        $this->assertEquals($decls [0]->getProperty(), 'border-width');
        $this->assertEquals($decls [0]->getValue(), '5px');
        $this->assertEquals($decls [1]->getProperty(), 'border-left-width');
        $this->assertEquals($decls [1]->getValue(), '5px');
        $this->assertEquals($decls [2]->getProperty(), 'border-right-width');
        $this->assertEquals($decls [2]->getValue(), '5px');
        $this->assertEquals($decls [3]->getProperty(), 'border-top-width');
        $this->assertEquals($decls [3]->getValue(), '5px');
        $this->assertEquals($decls [4]->getProperty(), 'border-bottom-width');
        $this->assertEquals($decls [4]->getValue(), '5px');

        $this->assertEquals($decls [5]->getProperty(), 'border-style');
        $this->assertEquals($decls [5]->getValue(), 'solid');
        $this->assertEquals($decls [6]->getProperty(), 'border-left-style');
        $this->assertEquals($decls [6]->getValue(), 'solid');
        $this->assertEquals($decls [7]->getProperty(), 'border-right-style');
        $this->assertEquals($decls [7]->getValue(), 'solid');
        $this->assertEquals($decls [8]->getProperty(), 'border-top-style');
        $this->assertEquals($decls [8]->getValue(), 'solid');
        $this->assertEquals($decls [9]->getProperty(), 'border-bottom-style');
        $this->assertEquals($decls [9]->getValue(), 'solid');

        $this->assertEquals($decls [10]->getProperty(), 'border-color');
        $this->assertEquals($decls [10]->getValue(), 'red');
        $this->assertEquals($decls [11]->getProperty(), 'border-left-color');
        $this->assertEquals($decls [11]->getValue(), 'red');
        $this->assertEquals($decls [12]->getProperty(), 'border-right-color');
        $this->assertEquals($decls [12]->getValue(), 'red');
        $this->assertEquals($decls [13]->getProperty(), 'border-top-color');
        $this->assertEquals($decls [13]->getValue(), 'red');
        $this->assertEquals($decls [14]->getProperty(), 'border-bottom-color');
        $this->assertEquals($decls [14]->getValue(), 'red');

        $this->assertEquals($decls [15]->getProperty(), 'border-left');
        $this->assertEquals($decls [15]->getValue(), '5px solid red');
        $this->assertEquals($decls [16]->getProperty(), 'border-right');
        $this->assertEquals($decls [16]->getValue(), '5px solid red');
        $this->assertEquals($decls [17]->getProperty(), 'border-top');
        $this->assertEquals($decls [17]->getValue(), '5px solid red');
        $this->assertEquals($decls [18]->getProperty(), 'border-bottom');
        $this->assertEquals($decls [18]->getValue(), '5px solid red');

    }

    /**
     * Ensure that the shorthand 'font' is exploded correctly.
     * Part 1.
     */
    public function test_font_shorthand_1() {
        /** @var css_declaration[] $decls */
        $decls = array();
        $decl = new css_declaration ('font', '15px arial, sans-serif;');
        $decl->explode ($decls);

        $this->assertEquals(count($decls), 6);
        $this->assertEquals($decls [0]->getProperty(), 'font-style');
        $this->assertEquals($decls [0]->getValue(), 'normal');
        $this->assertEquals($decls [1]->getProperty(), 'font-variant');
        $this->assertEquals($decls [1]->getValue(), 'normal');
        $this->assertEquals($decls [2]->getProperty(), 'font-weight');
        $this->assertEquals($decls [2]->getValue(), 'normal');
        $this->assertEquals($decls [3]->getProperty(), 'font-size');
        $this->assertEquals($decls [3]->getValue(), '15px');
        $this->assertEquals($decls [4]->getProperty(), 'line-height');
        $this->assertEquals($decls [4]->getValue(), 'normal');
        $this->assertEquals($decls [5]->getProperty(), 'font-family');
        $this->assertEquals($decls [5]->getValue(), 'arial, sans-serif');
    }

    /**
     * Ensure that the shorthand 'font' is exploded correctly.
     * Part 2.
     */
    public function test_font_shorthand_2() {
        /** @var css_declaration[] $decls */
        $decls = array();
        $decl = new css_declaration ('font', 'italic bold 12px/30px Georgia, serif;');
        $decl->explode ($decls);

        $this->assertEquals(count($decls), 6);
        $this->assertEquals($decls [0]->getProperty(), 'font-style');
        $this->assertEquals($decls [0]->getValue(), 'italic');
        $this->assertEquals($decls [1]->getProperty(), 'font-variant');
        $this->assertEquals($decls [1]->getValue(), 'normal');
        $this->assertEquals($decls [2]->getProperty(), 'font-weight');
        $this->assertEquals($decls [2]->getValue(), 'bold');
        $this->assertEquals($decls [3]->getProperty(), 'font-size');
        $this->assertEquals($decls [3]->getValue(), '12px');
        $this->assertEquals($decls [4]->getProperty(), 'line-height');
        $this->assertEquals($decls [4]->getValue(), '30px');
        $this->assertEquals($decls [5]->getProperty(), 'font-family');
        $this->assertEquals($decls [5]->getValue(), 'Georgia, serif');
    }

    /**
     * Ensure that the shorthand 'background' is exploded correctly.
     */
    public function test_background_shorthand() {
        /** @var css_declaration[] $decls */
        $decls = array();
        $decl = new css_declaration ('background', '#ffffff url("img_tree.png") no-repeat right top');
        $decl->explode ($decls);

        $this->assertEquals(count($decls), 5);
        $this->assertEquals($decls [0]->getProperty(), 'background-color');
        $this->assertEquals($decls [0]->getValue(), '#ffffff');
        $this->assertEquals($decls [1]->getProperty(), 'background-image');
        $this->assertEquals($decls [1]->getValue(), 'url("img_tree.png")');
        $this->assertEquals($decls [2]->getProperty(), 'background-repeat');
        $this->assertEquals($decls [2]->getValue(), 'no-repeat');
        $this->assertEquals($decls [3]->getProperty(), 'background-attachment');
        $this->assertEquals($decls [3]->getValue(), 'right');
        $this->assertEquals($decls [4]->getProperty(), 'background-position');
        $this->assertEquals($decls [4]->getValue(), 'top');
    }

    /**
     * Ensure that the shorthand 'padding' is exploded correctly.
     * Part 1.
     */
    public function test_padding_shorthand_1() {
        /** @var css_declaration[] $decls */
        $decls = array();
        $decl = new css_declaration ('padding', '25px 50px 75px 100px');
        $decl->explode ($decls);

        $this->assertEquals(count($decls), 4);
        $this->assertEquals($decls [0]->getProperty(), 'padding-top');
        $this->assertEquals($decls [0]->getValue(), '25px');
        $this->assertEquals($decls [1]->getProperty(), 'padding-right');
        $this->assertEquals($decls [1]->getValue(), '50px');
        $this->assertEquals($decls [2]->getProperty(), 'padding-bottom');
        $this->assertEquals($decls [2]->getValue(), '75px');
        $this->assertEquals($decls [3]->getProperty(), 'padding-left');
        $this->assertEquals($decls [3]->getValue(), '100px');
    }

    /**
     * Ensure that the shorthand 'padding' is exploded correctly.
     * Part 2.
     */
    public function test_padding_shorthand_2() {
        /** @var css_declaration[] $decls */
        $decls = array();
        $decl = new css_declaration ('padding', '25px 50px 75px');
        $decl->explode ($decls);

        $this->assertEquals(count($decls), 4);
        $this->assertEquals($decls [0]->getProperty(), 'padding-top');
        $this->assertEquals($decls [0]->getValue(), '25px');
        $this->assertEquals($decls [1]->getProperty(), 'padding-right');
        $this->assertEquals($decls [1]->getValue(), '50px');
        $this->assertEquals($decls [2]->getProperty(), 'padding-left');
        $this->assertEquals($decls [2]->getValue(), '50px');
        $this->assertEquals($decls [3]->getProperty(), 'padding-bottom');
        $this->assertEquals($decls [3]->getValue(), '75px');
    }

    /**
     * Ensure that the shorthand 'padding' is exploded correctly.
     * Part 3.
     */
    public function test_padding_shorthand_3() {
        /** @var css_declaration[] $decls */
        $decls = array();
        $decl = new css_declaration ('padding', '25px 50px');
        $decl->explode ($decls);

        $this->assertEquals(count($decls), 4);
        $this->assertEquals($decls [0]->getProperty(), 'padding-top');
        $this->assertEquals($decls [0]->getValue(), '25px');
        $this->assertEquals($decls [1]->getProperty(), 'padding-bottom');
        $this->assertEquals($decls [1]->getValue(), '25px');
        $this->assertEquals($decls [2]->getProperty(), 'padding-right');
        $this->assertEquals($decls [2]->getValue(), '50px');
        $this->assertEquals($decls [3]->getProperty(), 'padding-left');
        $this->assertEquals($decls [3]->getValue(), '50px');
    }

    /**
     * Ensure that the shorthand 'padding' is exploded correctly.
     * Part 4.
     */
    public function test_padding_shorthand_4() {
        /** @var css_declaration[] $decls */
        $decls = array();
        $decl = new css_declaration ('padding', '25px');
        $decl->explode ($decls);

        $this->assertEquals(count($decls), 4);
        $this->assertEquals($decls [0]->getProperty(), 'padding-top');
        $this->assertEquals($decls [0]->getValue(), '25px');
        $this->assertEquals($decls [1]->getProperty(), 'padding-bottom');
        $this->assertEquals($decls [1]->getValue(), '25px');
        $this->assertEquals($decls [2]->getProperty(), 'padding-right');
        $this->assertEquals($decls [2]->getValue(), '25px');
        $this->assertEquals($decls [3]->getProperty(), 'padding-left');
        $this->assertEquals($decls [3]->getValue(), '25px');
    }

    /**
     * Ensure that the shorthand 'margin' is exploded correctly.
     * Part 1.
     */
    public function test_margin_shorthand_1() {
        /** @var css_declaration[] $decls */
        $decls = array();
        $decl = new css_declaration ('margin', '25px 50px 75px 100px');
        $decl->explode ($decls);

        $this->assertEquals(count($decls), 4);
        $this->assertEquals($decls [0]->getProperty(), 'margin-top');
        $this->assertEquals($decls [0]->getValue(), '25px');
        $this->assertEquals($decls [1]->getProperty(), 'margin-right');
        $this->assertEquals($decls [1]->getValue(), '50px');
        $this->assertEquals($decls [2]->getProperty(), 'margin-bottom');
        $this->assertEquals($decls [2]->getValue(), '75px');
        $this->assertEquals($decls [3]->getProperty(), 'margin-left');
        $this->assertEquals($decls [3]->getValue(), '100px');
    }

    /**
     * Ensure that the shorthand 'margin' is exploded correctly.
     * Part 2.
     */
    public function test_margin_shorthand_2() {
        /** @var css_declaration[] $decls */
        $decls = array();
        $decl = new css_declaration ('margin', '25px 50px 75px');
        $decl->explode ($decls);

        $this->assertEquals(count($decls), 4);
        $this->assertEquals($decls [0]->getProperty(), 'margin-top');
        $this->assertEquals($decls [0]->getValue(), '25px');
        $this->assertEquals($decls [1]->getProperty(), 'margin-right');
        $this->assertEquals($decls [1]->getValue(), '50px');
        $this->assertEquals($decls [2]->getProperty(), 'margin-left');
        $this->assertEquals($decls [2]->getValue(), '50px');
        $this->assertEquals($decls [3]->getProperty(), 'margin-bottom');
        $this->assertEquals($decls [3]->getValue(), '75px');
    }

    /**
     * Ensure that the shorthand 'margin' is exploded correctly.
     * Part 3.
     */
    public function test_margin_shorthand_3() {
        /** @var css_declaration[] $decls */
        $decls = array();
        $decl = new css_declaration ('margin', '25px 50px');
        $decl->explode ($decls);

        $this->assertEquals(count($decls), 4);
        $this->assertEquals($decls [0]->getProperty(), 'margin-top');
        $this->assertEquals($decls [0]->getValue(), '25px');
        $this->assertEquals($decls [1]->getProperty(), 'margin-bottom');
        $this->assertEquals($decls [1]->getValue(), '25px');
        $this->assertEquals($decls [2]->getProperty(), 'margin-right');
        $this->assertEquals($decls [2]->getValue(), '50px');
        $this->assertEquals($decls [3]->getProperty(), 'margin-left');
        $this->assertEquals($decls [3]->getValue(), '50px');
    }

    /**
     * Ensure that the shorthand 'margin' is exploded correctly.
     * Part 4.
     */
    public function test_margin_shorthand_4() {
        /** @var css_declaration[] $decls */
        $decls = array();
        $decl = new css_declaration ('margin', '25px');
        $decl->explode ($decls);

        $this->assertEquals(count($decls), 4);
        $this->assertEquals($decls [0]->getProperty(), 'margin-top');
        $this->assertEquals($decls [0]->getValue(), '25px');
        $this->assertEquals($decls [1]->getProperty(), 'margin-bottom');
        $this->assertEquals($decls [1]->getValue(), '25px');
        $this->assertEquals($decls [2]->getProperty(), 'margin-right');
        $this->assertEquals($decls [2]->getValue(), '25px');
        $this->assertEquals($decls [3]->getProperty(), 'margin-left');
        $this->assertEquals($decls [3]->getValue(), '25px');
    }

    /**
     * Ensure that the shorthand 'list-style' is exploded correctly.
     * Part 1.
     */
    public function test_list_style_shorthand_1() {
        /** @var css_declaration[] $decls */
        $decls = array();
        $decl = new css_declaration ('list-style', 'square url("sqpurple.gif");');
        $decl->explode ($decls);

        $this->assertEquals(count($decls), 3);
        $this->assertEquals($decls [0]->getProperty(), 'list-style-type');
        $this->assertEquals($decls [0]->getValue(), 'square');
        $this->assertEquals($decls [1]->getProperty(), 'list-style-position');
        $this->assertEquals($decls [1]->getValue(), 'outside');
        $this->assertEquals($decls [2]->getProperty(), 'list-style-image');
        $this->assertEquals($decls [2]->getValue(), 'url("sqpurple.gif")');
    }

    /**
     * Ensure that the shorthand 'list-style' is exploded correctly.
     * Part 2.
     */
    public function test_list_style_shorthand_2() {
        /** @var css_declaration[] $decls */
        $decls = array();
        $decl = new css_declaration ('list-style', 'square inside url("sqpurple.gif");');
        $decl->explode ($decls);

        $this->assertEquals(count($decls), 3);
        $this->assertEquals($decls [0]->getProperty(), 'list-style-type');
        $this->assertEquals($decls [0]->getValue(), 'square');
        $this->assertEquals($decls [1]->getProperty(), 'list-style-position');
        $this->assertEquals($decls [1]->getValue(), 'inside');
        $this->assertEquals($decls [2]->getProperty(), 'list-style-image');
        $this->assertEquals($decls [2]->getValue(), 'url("sqpurple.gif")');
    }

    /**
     * Ensure that the shorthand 'flex' is exploded correctly.
     */
    public function test_flex_shorthand() {
        /** @var css_declaration[] $decls */
        $decls = array();
        $decl = new css_declaration ('flex', '1 2 200px');
        $decl->explode ($decls);

        $this->assertEquals(count($decls), 3);
        $this->assertEquals($decls [0]->getProperty(), 'flex-grow');
        $this->assertEquals($decls [0]->getValue(), '1');
        $this->assertEquals($decls [1]->getProperty(), 'flex-shrink');
        $this->assertEquals($decls [1]->getValue(), '2');
        $this->assertEquals($decls [2]->getProperty(), 'flex-basis');
        $this->assertEquals($decls [2]->getValue(), '200px');
    }

    /**
     * Ensure that the shorthand 'transition' is exploded correctly.
     */
    public function test_transition_shorthand() {
        /** @var css_declaration[] $decls */
        $decls = array();
        $decl = new css_declaration ('transition', 'width 2s linear 1s');
        $decl->explode ($decls);

        $this->assertEquals(count($decls), 4);
        $this->assertEquals($decls [0]->getProperty(), 'transition-property');
        $this->assertEquals($decls [0]->getValue(), 'width');
        $this->assertEquals($decls [1]->getProperty(), 'transition-duration');
        $this->assertEquals($decls [1]->getValue(), '2s');
        $this->assertEquals($decls [2]->getProperty(), 'transition-timing-function');
        $this->assertEquals($decls [2]->getValue(), 'linear');
        $this->assertEquals($decls [3]->getProperty(), 'transition-delay');
        $this->assertEquals($decls [3]->getValue(), '1s');
    }

    /**
     * Ensure that the shorthand 'outline' is exploded correctly.
     */
    public function test_outline_shorthand() {
        /** @var css_declaration[] $decls */
        $decls = array();
        $decl = new css_declaration ('outline', '#00FF00 dotted thick');
        $decl->explode ($decls);

        $this->assertEquals(count($decls), 3);
        $this->assertEquals($decls [0]->getProperty(), 'outline-color');
        $this->assertEquals($decls [0]->getValue(), '#00FF00');
        $this->assertEquals($decls [1]->getProperty(), 'outline-style');
        $this->assertEquals($decls [1]->getValue(), 'dotted');
        $this->assertEquals($decls [2]->getProperty(), 'outline-width');
        $this->assertEquals($decls [2]->getValue(), 'thick');
    }

    /**
     * Ensure that the shorthand 'animation' is exploded correctly.
     * Part 1.
     */
    public function test_animation_shorthand_1() {
        /** @var css_declaration[] $decls */
        $decls = array();
        $decl = new css_declaration ('animation', 'mymove 5s infinite;');
        $decl->explode ($decls);

        $this->assertEquals(count($decls), 3);
        $this->assertEquals($decls [0]->getProperty(), 'animation-name');
        $this->assertEquals($decls [0]->getValue(), 'mymove');
        $this->assertEquals($decls [1]->getProperty(), 'animation-duration');
        $this->assertEquals($decls [1]->getValue(), '5s');
        $this->assertEquals($decls [2]->getProperty(), 'animation-timing-function');
        $this->assertEquals($decls [2]->getValue(), 'infinite');
    }

    /**
     * Ensure that the shorthand 'animation' is exploded correctly.
     * Part 2.
     */
    public function test_animation_shorthand_2() {
        /** @var css_declaration[] $decls */
        $decls = array();
        $decl = new css_declaration ('animation', 'mymove 5s infinite 2s 3 normal forwards paused;');
        $decl->explode ($decls);

        $this->assertEquals(count($decls), 8);
        $this->assertEquals($decls [0]->getProperty(), 'animation-name');
        $this->assertEquals($decls [0]->getValue(), 'mymove');
        $this->assertEquals($decls [1]->getProperty(), 'animation-duration');
        $this->assertEquals($decls [1]->getValue(), '5s');
        $this->assertEquals($decls [2]->getProperty(), 'animation-timing-function');
        $this->assertEquals($decls [2]->getValue(), 'infinite');
        $this->assertEquals($decls [3]->getProperty(), 'animation-delay');
        $this->assertEquals($decls [3]->getValue(), '2s');
        $this->assertEquals($decls [4]->getProperty(), 'animation-iteration-count');
        $this->assertEquals($decls [4]->getValue(), '3');
        $this->assertEquals($decls [5]->getProperty(), 'animation-direction');
        $this->assertEquals($decls [5]->getValue(), 'normal');
        $this->assertEquals($decls [6]->getProperty(), 'animation-fill-mode');
        $this->assertEquals($decls [6]->getValue(), 'forwards');
        $this->assertEquals($decls [7]->getProperty(), 'animation-play-state');
        $this->assertEquals($decls [7]->getValue(), 'paused');
    }

    /**
     * Ensure that the shorthand 'border-bottom' is exploded correctly.
     */
    public function test_border_bottom_shorthand() {
        /** @var css_declaration[] $decls */
        $decls = array();
        $decl = new css_declaration ('border-bottom', 'thick dotted #ff0000;');
        $decl->explode ($decls);

        $this->assertEquals(count($decls), 3);
        $this->assertEquals($decls [0]->getProperty(), 'border-bottom-width');
        $this->assertEquals($decls [0]->getValue(), 'thick');
        $this->assertEquals($decls [1]->getProperty(), 'border-bottom-style');
        $this->assertEquals($decls [1]->getValue(), 'dotted');
        $this->assertEquals($decls [2]->getProperty(), 'border-bottom-color');
        $this->assertEquals($decls [2]->getValue(), '#ff0000');
    }

    /**
     * Ensure that the shorthand 'columns' is exploded correctly.
     */
    public function test_columns_shorthand() {
        /** @var css_declaration[] $decls */
        $decls = array();
        $decl = new css_declaration ('columns', '100px 3');
        $decl->explode ($decls);

        $this->assertEquals(count($decls), 2);
        $this->assertEquals($decls [0]->getProperty(), 'column-width');
        $this->assertEquals($decls [0]->getValue(), '100px');
        $this->assertEquals($decls [1]->getProperty(), 'column-count');
        $this->assertEquals($decls [1]->getValue(), '3');
    }

    /**
     * Ensure that @media queries are understood.
     * Part 1.
     */
    public function test_media_queries_part1() {
        $properties = array();
        $css_code = 'p {
                         background-color:blue;
                     }

                     @media print {
                     p {
                         background-color:white;
                     }
                     }';

        // Import CSS code
        $import = new helper_plugin_odt_cssimportnew ();
        $import->importFromString ($css_code);

        // Create element to match
        $state = new helper_plugin_odt_cssdocument();
        $state->open('p');

        // Query properties.
        $import->getPropertiesForElement ($properties, $state->getCurrentElement());

        // Only for debugging.
        //print ($import->rulesToString());

        $this->assertEquals(count($properties), 1);
        $this->assertEquals('blue', $properties ['background-color']);
    }

    /**
     * Ensure that @media queries are understood.
     * Part 2.
     */
    public function test_media_queries_part2() {
        $properties = array();
        $css_code = 'p {
                         background-color:blue;
                     }

                     @media print {
                     p {
                         background-color:white;
                     }
                     }';

        // Import CSS code
        $import = new helper_plugin_odt_cssimportnew ();
        $import->importFromString ($css_code);

        // Create element to match
        $state = new helper_plugin_odt_cssdocument();
        $state->open('p');

        // Query properties.
        $import->setMedia('print');
        $import->getPropertiesForElement ($properties, $state->getCurrentElement());

        $this->assertEquals(count($properties), 1);
        $this->assertEquals('white', $properties ['background-color']);
    }

    /**
     * Ensure that @media queries are understood.
     * Part 3.
     */
    public function test_media_queries_part3() {
        $properties = array();
        $css_code = '@media only screen and (max-width: 500px) {
                     p {
                         background-color:blue;
                     }
                     }

                     @media print {
                     p {
                         background-color:white;
                     }
                     }';

        // Import CSS code
        $import = new helper_plugin_odt_cssimportnew ();
        $import->importFromString ($css_code);

        // Create element to match
        $state = new helper_plugin_odt_cssdocument();
        $state->open('p');

        // Query properties.
        $import->getPropertiesForElement ($properties, $state->getCurrentElement());

        // We shouldn't get any properties
        $this->assertEquals(0, count($properties));
    }

    /**
     * Ensure that @media queries are understood.
     * Part 4.
     */
    public function test_media_queries_part4() {
        $properties = array();
        $css_code = '@media screen {
                     p {
                         background-color:blue;
                     }

                     p {
                         color:red;
                     }
                     }

                     @media print {
                     p {
                         background-color:white;
                     }
                     }';

        // Import CSS code
        $import = new helper_plugin_odt_cssimportnew ();
        $import->importFromString ($css_code);

        // Create element to match
        $state = new helper_plugin_odt_cssdocument();
        $state->open('p');

        // Query properties.
        $import->setMedia('print');
        $import->getPropertiesForElement ($properties, $state->getCurrentElement());

        // Check properties
        $this->assertEquals(1, count($properties));
        $this->assertEquals('white', $properties ['background-color']);
        $this->assertEquals(NULL, $properties ['color']);
    }

    /**
     * Test if selector properly matches type/element.
     */
    public function test_selector_type() {
        $selector = new css_selector ('h1');

        $state = new helper_plugin_odt_cssdocument();
        $state->open('h1');

        $this->assertEquals(true, $selector->matches($state->getCurrentElement(), $specificity));

        $state = new helper_plugin_odt_cssdocument();
        $state->open('p');

        $this->assertEquals(false, $selector->matches($state->getCurrentElement(), $specificity));
    }

    /**
     * Test if selector properly matches class.
     */
    public function test_selector_class() {
        $selector = new css_selector ('.test');

        $state = new helper_plugin_odt_cssdocument();
        $state->open('p', 'class="test"');

        $this->assertEquals(true, $selector->matches($state->getCurrentElement(), $specificity));

        $state = new helper_plugin_odt_cssdocument();
        $state->open('p', 'class="otherclass"');

        $this->assertEquals(false, $selector->matches($state->getCurrentElement(), $specificity));
    }

    /**
     * Test if selector properly matches multiple classes.
     */
    public function test_selector_multiple_classes() {
        $selector = new css_selector ('.foo.test');

        // Match: p has class test and foo.
        $state = new helper_plugin_odt_cssdocument();
        $state->open('p', 'class="test foo"');

        $this->assertEquals(true, $selector->matches($state->getCurrentElement(), $specificity));

        // Match: p has class test and foo and more.
        $state = new helper_plugin_odt_cssdocument();
        $state->open('p', 'class="test abc foo otherclass"');

        $this->assertEquals(true, $selector->matches($state->getCurrentElement(), $specificity));

        // No match: p has class foo but test is missing.
        $state = new helper_plugin_odt_cssdocument();
        $state->open('p', 'class="otherclass foo"');

        $this->assertEquals(false, $selector->matches($state->getCurrentElement(), $specificity));

        // No match: p has class test but foo is missing.
        $state = new helper_plugin_odt_cssdocument();
        $state->open('p', 'class="test ootherclass"');

        $this->assertEquals(false, $selector->matches($state->getCurrentElement(), $specificity));

        // No match: class test and foo missing.
        $state = new helper_plugin_odt_cssdocument();
        $state->open('p', 'class="abc ootherclass"');

        $this->assertEquals(false, $selector->matches($state->getCurrentElement(), $specificity));

        // No match: no class.
        $state = new helper_plugin_odt_cssdocument();
        $state->open('p');

        $this->assertEquals(false, $selector->matches($state->getCurrentElement(), $specificity));
    }

    /**
     * Test if selector properly matches id.
     */
    public function test_selector_id() {
        $selector = new css_selector ('#123');

        $state = new helper_plugin_odt_cssdocument();
        $state->open('p', 'id="123"');

        $this->assertEquals(true, $selector->matches($state->getCurrentElement(), $specificity));

        $state = new helper_plugin_odt_cssdocument();
        $state->open('p', 'id="124"');

        $this->assertEquals(false, $selector->matches($state->getCurrentElement(), $specificity));
    }

    /**
     * Test if selector properly matches attribute presence.
     */
    public function test_selector_attributes_presence() {
        $selector = new css_selector ('p[lang]');

        // Match: lang present
        $state = new helper_plugin_odt_cssdocument();
        $state->open('p', 'lang="de"');

        $this->assertEquals(true, $selector->matches($state->getCurrentElement(), $specificity));

        // Match: lang present
        $state = new helper_plugin_odt_cssdocument();
        $state->open('p', 'other1="value1" lang="de" other2="value2"');

        $this->assertEquals(true, $selector->matches($state->getCurrentElement(), $specificity));

        // No match: lang present
        $state = new helper_plugin_odt_cssdocument();
        $state->open('p', 'lang="en"');

        $this->assertEquals(true, $selector->matches($state->getCurrentElement(), $specificity));
    }

    /**
     * Test if selector properly matches attribute value.
     */
    public function test_selector_attributes_match() {
        $selector = new css_selector ('p[lang="de"]');

        // Match: lang has value 'de'
        $state = new helper_plugin_odt_cssdocument();
        $state->open('p', 'lang="de"');

        $this->assertEquals(true, $selector->matches($state->getCurrentElement(), $specificity));

        // Match: lang has value 'de'
        $state = new helper_plugin_odt_cssdocument();
        $state->open('p', 'other1="value1" lang="de" other2="value2"');

        $this->assertEquals(true, $selector->matches($state->getCurrentElement(), $specificity));

        // No match: lang has value 'en'
        $state = new helper_plugin_odt_cssdocument();
        $state->open('p', 'lang="en"');

        $this->assertEquals(false, $selector->matches($state->getCurrentElement(), $specificity));
    }

    /**
     * Test if selector properly matches multiple attributes value.
     */
    public function test_selector_attributes_match_multiple() {
        $selector = new css_selector ('p[lang="de"][test="123"]');

        // No match: lang has value 'de', test is missing
        $state = new helper_plugin_odt_cssdocument();
        $state->open('p', 'lang="de"');

        $this->assertEquals(false, $selector->matches($state->getCurrentElement(), $specificity));

        // No match: test has value '123', lang is missing
        $state = new helper_plugin_odt_cssdocument();
        $state->open('p', 'test="123"');

        $this->assertEquals(false, $selector->matches($state->getCurrentElement(), $specificity));

        // Match: lang has value 'de', test has value '123'
        $state = new helper_plugin_odt_cssdocument();
        $state->open('p', 'other1="value1" lang="de" test="123"');

        $this->assertEquals(true, $selector->matches($state->getCurrentElement(), $specificity));

        // No match: lang has value 'en', test has value '123'
        $state = new helper_plugin_odt_cssdocument();
        $state->open('p', 'lang="en" test="123"');

        $this->assertEquals(false, $selector->matches($state->getCurrentElement(), $specificity));

        // No match: lang has value 'de', test has value '456'
        $state = new helper_plugin_odt_cssdocument();
        $state->open('p', 'lang="de" test="456"');

        $this->assertEquals(false, $selector->matches($state->getCurrentElement(), $specificity));
    }

    /**
     * Test if selector properly matches attribute value
     * as a word.
     */
    public function test_selector_attributes_match_word() {
        $selector = new css_selector ('p[foo~="test"]');

        // Match: foo has value 'test'
        $state = new helper_plugin_odt_cssdocument();
        $state->open('p', 'foo="test"');

        $this->assertEquals(true, $selector->matches($state->getCurrentElement(), $specificity));

        // Match: foo has value 'abc test'
        $state = new helper_plugin_odt_cssdocument();
        $state->open('p', 'foo="abc test"');

        $this->assertEquals(true, $selector->matches($state->getCurrentElement(), $specificity));

        // Match: foo has value 'abc test 456'
        $state = new helper_plugin_odt_cssdocument();
        $state->open('p', 'foo="abc test 456"');

        $this->assertEquals(true, $selector->matches($state->getCurrentElement(), $specificity));

        // No match: foo has value 'test123'
        $state = new helper_plugin_odt_cssdocument();
        $state->open('p', 'foo="test123"');

        $this->assertEquals(false, $selector->matches($state->getCurrentElement(), $specificity));

        // No match: foo has value 'ABCtest'
        $state = new helper_plugin_odt_cssdocument();
        $state->open('p', 'foo="ABCtest"');

        $this->assertEquals(false, $selector->matches($state->getCurrentElement(), $specificity));
    }

    /**
     * Test if selector properly matches attribute value
     * as a word or the beginning of a word followed by a hyphen.
     */
    public function test_selector_attributes_match_full_or_start() {
        $selector = new css_selector ('p[foo|="test"]');

        // Match: foo has value 'test'
        $state = new helper_plugin_odt_cssdocument();
        $state->open('p', 'foo="test"');

        $this->assertEquals(true, $selector->matches($state->getCurrentElement(), $specificity));

        // Match: foo has value 'test-123'
        $state = new helper_plugin_odt_cssdocument();
        $state->open('p', 'foo="test-123"');

        $this->assertEquals(true, $selector->matches($state->getCurrentElement(), $specificity));

        // No match: foo has value 'test123'
        $state = new helper_plugin_odt_cssdocument();
        $state->open('p', 'foo="test123"');

        $this->assertEquals(false, $selector->matches($state->getCurrentElement(), $specificity));

        // No match: foo has value '123-test'
        $state = new helper_plugin_odt_cssdocument();
        $state->open('p', 'foo="123-test"');

        $this->assertEquals(false, $selector->matches($state->getCurrentElement(), $specificity));
    }

    /**
     * Test if selector properly matches attribute value as prefix.
     */
    public function test_selector_attributes_match_prefix() {
        $selector = new css_selector ('p[foo^="test"]');

        // Match: foo has value 'test'
        $state = new helper_plugin_odt_cssdocument();
        $state->open('p', 'foo="test"');

        $this->assertEquals(true, $selector->matches($state->getCurrentElement(), $specificity));

        // Match: foo has value 'test123'
        $state = new helper_plugin_odt_cssdocument();
        $state->open('p', 'foo="test123"');

        $this->assertEquals(true, $selector->matches($state->getCurrentElement(), $specificity));

        // No match: foo has value '123test'
        $state = new helper_plugin_odt_cssdocument();
        $state->open('p', 'foo="123test"');

        $this->assertEquals(false, $selector->matches($state->getCurrentElement(), $specificity));
    }

    /**
     * Test if selector properly matches attribute value as suffix.
     */
    public function test_selector_attributes_match_suffix() {
        $selector = new css_selector ('p[foo$="test"]');

        // Match: foo has value 'test'
        $state = new helper_plugin_odt_cssdocument();
        $state->open('p', 'foo="test"');

        $this->assertEquals(true, $selector->matches($state->getCurrentElement(), $specificity));

        // Match: foo has value '123test'
        $state = new helper_plugin_odt_cssdocument();
        $state->open('p', 'foo="123test"');

        $this->assertEquals(true, $selector->matches($state->getCurrentElement(), $specificity));

        // No match: foo has value 'test123'
        $state = new helper_plugin_odt_cssdocument();
        $state->open('p', 'foo="test123"');

        $this->assertEquals(false, $selector->matches($state->getCurrentElement(), $specificity));
    }

    /**
     * Test if selector properly matches attribute value as substring.
     */
    public function test_selector_attributes_match_substring() {
        $selector = new css_selector ('p[foo*="test"]');

        // Match: foo has value 'test'
        $state = new helper_plugin_odt_cssdocument();
        $state->open('p', 'foo="test"');

        $this->assertEquals(true, $selector->matches($state->getCurrentElement(), $specificity));

        // Match: foo has value '123test'
        $state = new helper_plugin_odt_cssdocument();
        $state->open('p', 'foo="123test"');

        $this->assertEquals(true, $selector->matches($state->getCurrentElement(), $specificity));

        // Match: foo has value 'test123'
        $state = new helper_plugin_odt_cssdocument();
        $state->open('p', 'foo="test123"');

        $this->assertEquals(true, $selector->matches($state->getCurrentElement(), $specificity));

        // Match: foo has value '456test123'
        $state = new helper_plugin_odt_cssdocument();
        $state->open('p', 'foo="456test123"');

        $this->assertEquals(true, $selector->matches($state->getCurrentElement(), $specificity));

        // Match: foo has value '456te st123'
        $state = new helper_plugin_odt_cssdocument();
        $state->open('p', 'foo="456te st123"');

        $this->assertEquals(false, $selector->matches($state->getCurrentElement(), $specificity));
    }

    /**
     * Test if selector properly matches universal selector.
     */
    public function test_selector_universal() {
        $selector = new css_selector ('*');

        $state = new helper_plugin_odt_cssdocument();
        $state->open('p');

        $this->assertEquals(true, $selector->matches($state->getCurrentElement(), $specificity));

        $state = new helper_plugin_odt_cssdocument();
        $state->open('h1');

        $this->assertEquals(true, $selector->matches($state->getCurrentElement(), $specificity));

        $state = new helper_plugin_odt_cssdocument();
        $state->open('h1', 'id="123"');

        $this->assertEquals(true, $selector->matches($state->getCurrentElement(), $specificity));

        $state = new helper_plugin_odt_cssdocument();
        $state->open('h1', 'class="someclass"');

        $this->assertEquals(true, $selector->matches($state->getCurrentElement(), $specificity));
    }

    /**
     * Test if selector properly matches type and class.
     */
    public function test_selector_type_and_class() {
        $selector = new css_selector ('h1.test');

        // No class.
        $state = new helper_plugin_odt_cssdocument();
        $state->open('h1');

        $this->assertEquals(false, $selector->matches($state->getCurrentElement(), $specificity));

        // Match.
        $state = new helper_plugin_odt_cssdocument();
        $state->open('h1', 'class="test"');

        $this->assertEquals(true, $selector->matches($state->getCurrentElement(), $specificity));

        // Wrong class.
        $state = new helper_plugin_odt_cssdocument();
        $state->open('h1', 'class="wrong"');

        $this->assertEquals(false, $selector->matches($state->getCurrentElement(), $specificity));

        // Wrong type, no class.
        $state = new helper_plugin_odt_cssdocument();
        $state->open('p');

        $this->assertEquals(false, $selector->matches($state->getCurrentElement(), $specificity));

        // Wrong type, correct class.
        $state = new helper_plugin_odt_cssdocument();
        $state->open('p', 'class="test"');

        $this->assertEquals(false, $selector->matches($state->getCurrentElement(), $specificity));

        // Wrong type, wrong class.
        $state = new helper_plugin_odt_cssdocument();
        $state->open('p', 'class="wrong"');

        $this->assertEquals(false, $selector->matches($state->getCurrentElement(), $specificity));
    }

    /**
     * Test if selector properly matches a group of selectors.
     */
    public function test_selector_group() {
        $selector = new css_selector ('h1, a, p');

        $state = new helper_plugin_odt_cssdocument();
        $state->open('h1');

        $this->assertEquals(true, $selector->matches($state->getCurrentElement(), $specificity));

        $state = new helper_plugin_odt_cssdocument();
        $state->open('a');

        $this->assertEquals(true, $selector->matches($state->getCurrentElement(), $specificity));

        $state = new helper_plugin_odt_cssdocument();
        $state->open('p');

        $this->assertEquals(true, $selector->matches($state->getCurrentElement(), $specificity));

        $state = new helper_plugin_odt_cssdocument();
        $state->open('h2');

        $this->assertEquals(false, $selector->matches($state->getCurrentElement(), $specificity));
    }

    /**
     * Test if selector properly matches the descendant combinator.
     */
    public function test_selector_combinator_descendant() {
        $selector = new css_selector ('div p');

        // Match: p inside div
        $state = new helper_plugin_odt_cssdocument();
        $state->open('div');
        $state->open('p');

        $this->assertEquals(true, $selector->matches($state->getCurrentElement(), $specificity));

        // Match: p inside h1 inside div
        $state = new helper_plugin_odt_cssdocument();
        $state->open('div');
        $state->open('h1');
        $state->open('p');

        $this->assertEquals(true, $selector->matches($state->getCurrentElement(), $specificity));

        // No match: a inside div
        $state = new helper_plugin_odt_cssdocument();
        $state->open('div');
        $state->open('a');

        $this->assertEquals(false, $selector->matches($state->getCurrentElement(), $specificity));

        // No match: single p
        $state = new helper_plugin_odt_cssdocument();
        $state->open('p');

        $this->assertEquals(false, $selector->matches($state->getCurrentElement(), $specificity));

        // No match: p inside td
        $state = new helper_plugin_odt_cssdocument();
        $state->open('td');
        $state->open('p');

        $this->assertEquals(false, $selector->matches($state->getCurrentElement(), $specificity));

        // No match: p after div
        $state = new helper_plugin_odt_cssdocument();
        $state->open('div');
        $state->close('div');
        $state->open('p');

        $this->assertEquals(false, $selector->matches($state->getCurrentElement(), $specificity));
    }

    /**
     * Test if selector properly matches the child combinator.
     */
    public function test_selector_combinator_child() {
        $selector = new css_selector ('div > p');

        // Match: p inside div
        $state = new helper_plugin_odt_cssdocument();
        $state->open('div');
        $state->open('p');

        $this->assertEquals(true, $selector->matches($state->getCurrentElement(), $specificity));

        // No match: p inside h1 inside div
        $state = new helper_plugin_odt_cssdocument();
        $state->open('div');
        $state->open('h1');
        $state->open('p');

        $this->assertEquals(false, $selector->matches($state->getCurrentElement(), $specificity));

        // No match: a inside div
        $state = new helper_plugin_odt_cssdocument();
        $state->open('div');
        $state->open('a');

        $this->assertEquals(false, $selector->matches($state->getCurrentElement(), $specificity));

        // No match: single p
        $state = new helper_plugin_odt_cssdocument();
        $state->open('p');

        $this->assertEquals(false, $selector->matches($state->getCurrentElement(), $specificity));

        // No match: p inside td
        $state = new helper_plugin_odt_cssdocument();
        $state->open('td');
        $state->open('p');

        $this->assertEquals(false, $selector->matches($state->getCurrentElement(), $specificity));

        // No match: p after div
        $state = new helper_plugin_odt_cssdocument();
        $state->open('div');
        $state->close('div');
        $state->open('p');

        $this->assertEquals(false, $selector->matches($state->getCurrentElement(), $specificity));
    }

    /**
     * Test if selector properly matches the adjacent sibling combinator.
     */
    public function test_selector_combinator_adjacent_sibling() {
        $selector = new css_selector ('a + p');

        // Match: p imediately following a
        $state = new helper_plugin_odt_cssdocument();
        $state->open('a');
        $state->close('a');
        $state->open('p');

        $this->assertEquals(true, $selector->matches($state->getCurrentElement(), $specificity));

        // No match: p following a, not imediately
        $state = new helper_plugin_odt_cssdocument();
        $state->open('a');
        $state->close('a');
        $state->open('h1');
        $state->close('h1');
        $state->open('p');

        $this->assertEquals(false, $selector->matches($state->getCurrentElement(), $specificity));

        // No match: p inside a
        $state = new helper_plugin_odt_cssdocument();
        $state->open('a');
        $state->open('p');

        $this->assertEquals(false, $selector->matches($state->getCurrentElement(), $specificity));

        // No match: single p
        $state = new helper_plugin_odt_cssdocument();
        $state->open('p');

        $this->assertEquals(false, $selector->matches($state->getCurrentElement(), $specificity));

        // No match: p imediately following h1
        $state = new helper_plugin_odt_cssdocument();
        $state->open('h1');
        $state->close('h1');
        $state->open('p');

        $this->assertEquals(false, $selector->matches($state->getCurrentElement(), $specificity));
    }

    /**
     * Test if selector properly matches the general sibling combinator.
     */
    public function test_selector_combinator_general_sibling() {
        $selector = new css_selector ('a ~ p');

        // Match: p imediately following a
        $state = new helper_plugin_odt_cssdocument();
        $state->open('a');
        $state->close('a');
        $state->open('p');

        $this->assertEquals(true, $selector->matches($state->getCurrentElement(), $specificity));

        // Match: p following a, not imediately
        $state = new helper_plugin_odt_cssdocument();
        $state->open('a');
        $state->close('a');
        $state->open('h1');
        $state->close('h1');
        $state->open('p');

        $this->assertEquals(true, $selector->matches($state->getCurrentElement(), $specificity));

        // No match: p inside a
        $state = new helper_plugin_odt_cssdocument();
        $state->open('a');
        $state->open('p');

        $this->assertEquals(false, $selector->matches($state->getCurrentElement(), $specificity));

        // No match: single p
        $state = new helper_plugin_odt_cssdocument();
        $state->open('p');

        $this->assertEquals(false, $selector->matches($state->getCurrentElement(), $specificity));

        // No match: p imediately following h1
        $state = new helper_plugin_odt_cssdocument();
        $state->open('h1');
        $state->close('h1');
        $state->open('p');

        $this->assertEquals(false, $selector->matches($state->getCurrentElement(), $specificity));
    }

    /**
     * Test if CSS import properly handles selector specificity
     * (less specific selector comes first in CSS code)
     */
    public function test_specificity_part1() {
        $properties = array();
        $css_code = 'div + p {
                         color: red;
                         background-color: yellow;
                     }
                     div + p.test {
                         background-color: green;
                     }';

        // Import CSS code
        $import = new helper_plugin_odt_cssimportnew ();
        $import->importFromString ($css_code);

        // Create element to match
        $state = new helper_plugin_odt_cssdocument();
        $state->open('div');
        $state->close('div');
        $state->open('p', 'class="test"');

        // Query properties.
        $import->getPropertiesForElement ($properties, $state->getCurrentElement());

        // Only for debugging.
        //print ($import->rulesToString());

        $this->assertEquals(2, count($properties));
        $this->assertEquals('green', $properties ['background-color']);
        $this->assertEquals('red', $properties ['color']);
    }

    /**
     * Test if CSS import properly handles selector specificity
     * (more specific selector comes first in CSS code)
     */
    public function test_specificity_part2() {
        $properties = array();
        $css_code = 'div + p.test {
                         background-color: green;
                     }
                     div + p {
                         color: red;
                         background-color: yellow;
                     }';

        // Import CSS code
        $import = new helper_plugin_odt_cssimportnew ();
        $import->importFromString ($css_code);

        // Create element to match
        $state = new helper_plugin_odt_cssdocument();
        $state->open('div');
        $state->close('div');
        $state->open('p', 'class="test"');

        // Query properties.
        $import->getPropertiesForElement ($properties, $state->getCurrentElement());

        // Only for debugging.
        //print ($import->rulesToString());

        $this->assertEquals(2, count($properties));
        $this->assertEquals('green', $properties ['background-color']);
        $this->assertEquals('red', $properties ['color']);
    }

    /**
     * Test if selector properly matches given pseudo class.
     */
    public function test_selector_pseudo_class() {
        $selector = new css_selector ('a:visited');

        // Match: element a with pseudo class 'visited'
        $state = new helper_plugin_odt_cssdocument();
        $state->open('a', NULL, 'visited');

        $this->assertEquals(true, $selector->matches($state->getCurrentElement(), $specificity));

        // No match: element a without pseudo class
        $state = new helper_plugin_odt_cssdocument();
        $state->open('a', NULL, NULL);

        $this->assertEquals(false, $selector->matches($state->getCurrentElement(), $specificity));

        // No match: element a with other pseudo class
        $state = new helper_plugin_odt_cssdocument();
        $state->open('a', NULL, 'hover');

        $this->assertEquals(false, $selector->matches($state->getCurrentElement(), $specificity));
    }

    /**
     * Test if selector properly matches multiple pseudo class.
     */
    public function test_selector_multiple_pseudo_classes() {
        $selector = new css_selector ('a:visited:first-child');

        // Match: element a with pseudo class 'visited' and 'first-child'
        $state = new helper_plugin_odt_cssdocument();
        $state->open('a', NULL, 'visited first-child');

        $this->assertEquals(true, $selector->matches($state->getCurrentElement(), $specificity));

        // Match: element a with pseudo class 'visited' and 'first-child'
        // and more
        $state = new helper_plugin_odt_cssdocument();
        $state->open('a', NULL, 'visited first-child valid');

        $this->assertEquals(true, $selector->matches($state->getCurrentElement(), $specificity));

        // No match: element a with pseudo class 'visited' only
        $state = new helper_plugin_odt_cssdocument();
        $state->open('a', NULL, 'visited');

        $this->assertEquals(false, $selector->matches($state->getCurrentElement(), $specificity));

        // No match: element a with pseudo class 'first-child' only
        $state = new helper_plugin_odt_cssdocument();
        $state->open('a', NULL, 'first-child');

        $this->assertEquals(false, $selector->matches($state->getCurrentElement(), $specificity));

        // No match: element a without pseudo class
        $state = new helper_plugin_odt_cssdocument();
        $state->open('a', NULL, NULL);

        $this->assertEquals(false, $selector->matches($state->getCurrentElement(), $specificity));

        // No match: element a with other pseudo class
        $state = new helper_plugin_odt_cssdocument();
        $state->open('a', NULL, 'hover');

        $this->assertEquals(false, $selector->matches($state->getCurrentElement(), $specificity));
    }

    /**
     * Test if selector properly matches given pseudo element.
     */
    public function test_selector_pseudo_element() {
        $selector = new css_selector ('a::first-letter');

        // Match: element a with pseudo class 'visited'
        $state = new helper_plugin_odt_cssdocument();
        $state->open('a', NULL, NULL, 'first-letter');

        $this->assertEquals(true, $selector->matches($state->getCurrentElement(), $specificity));

        // No match: element a without pseudo element
        $state = new helper_plugin_odt_cssdocument();
        $state->open('a', NULL, NULL, NULL);

        $this->assertEquals(false, $selector->matches($state->getCurrentElement(), $specificity));

        // No match: element a with other pseudo element
        $state = new helper_plugin_odt_cssdocument();
        $state->open('a', NULL, NULL, 'first-line');

        $this->assertEquals(false, $selector->matches($state->getCurrentElement(), $specificity));
    }
}
