<?php

require_once DOKU_INC.'lib/plugins/odt/helper/cssimport.php';

/**
 * Tests to ensure functionality of the CSS import classes.
 *
 * @group plugin_odt
 * @group plugins
 */
class plugin_odt_cssimport_test extends DokuWikiTest {
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

        $import = new helper_plugin_odt_cssimport ();
        $import->importFromString ($css_code);
        $import->getPropertiesForElement ($properties, 'p', NULL);

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

        $import = new helper_plugin_odt_cssimport ();
        $import->importFromString ($css_code);
        $import->getPropertiesForElement ($properties, 'p', NULL, 'print');

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

        $import = new helper_plugin_odt_cssimport ();
        $import->importFromString ($css_code);
        $import->getPropertiesForElement ($properties, 'p', NULL);

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

        $import = new helper_plugin_odt_cssimport ();
        $import->importFromString ($css_code);
        $import->getPropertiesForElement ($properties, 'p', NULL, 'print');

        // Check properties
        $this->assertEquals(1, count($properties));
        $this->assertEquals('white', $properties ['background-color']);
        $this->assertEquals(NULL, $properties ['color']);
    }

    /**
     * Test more complicated CSS parsing with dw and wrap CSS.
     * Part 1.
     */
    public function test_dw_and_wrap_css_part1 () {
        $properties = array();

        $import = new helper_plugin_odt_cssimport ();
        $import->importFromFile (TMP_DIR . '/data/dw_css_with_wrap.css');
        $import->getPropertiesForElement ($properties, 'div', 'dokuwiki wrap_help', 'only screen and (max-width: 600px)');

        // For debugging: this will write the parsed/imported CSS in the file
        // /tmp/dwtests-xxx.yyy/data/odt_parsed.css
        //$handle = fopen (TMP_DIR . '/data/odt_parsed.css', 'w');
        //fwrite ($handle, $import->rulesToString());
        //fclose ($handle);

        // Check properties
        $this->assertEquals(33, count($properties));
        $this->assertEquals('1em 1em .5em', $properties ['padding']);
        $this->assertEquals('1em', $properties ['padding-top']);
        $this->assertEquals('1em', $properties ['padding-right']);
        $this->assertEquals('.5em', $properties ['padding-bottom']);
        $this->assertEquals('1em', $properties ['padding-left']);
        $this->assertEquals('1.5em', $properties ['margin-bottom']);
        $this->assertEquals('68px', $properties ['min-height']);
        $this->assertEquals('10px 50%', $properties ['background-position']);
        $this->assertEquals('no-repeat', $properties ['background-repeat']);
        $this->assertEquals('inherit', $properties ['color']);
        $this->assertEquals('hidden', $properties ['overflow']);
        $this->assertEquals('#dcc2ef', $properties ['background-color']);
        $this->assertEquals('url(/lib/plugins/wrap/images/note/48/help.png)', $properties ['background-image']);
        $this->assertEquals('2px solid #999', $properties ['border']);
        $this->assertEquals('2px solid #999', $properties ['border-left']);
        $this->assertEquals('2px solid #999', $properties ['border-right']);
        $this->assertEquals('2px solid #999', $properties ['border-top']);
        $this->assertEquals('2px solid #999', $properties ['border-bottom']);
        $this->assertEquals('2px', $properties ['border-width']);
        $this->assertEquals('2px', $properties ['border-left-width']);
        $this->assertEquals('2px', $properties ['border-right-width']);
        $this->assertEquals('2px', $properties ['border-top-width']);
        $this->assertEquals('2px', $properties ['border-bottom-width']);
        $this->assertEquals('solid', $properties ['border-style']);
        $this->assertEquals('solid', $properties ['border-left-style']);
        $this->assertEquals('solid', $properties ['border-right-style']);
        $this->assertEquals('solid', $properties ['border-top-style']);
        $this->assertEquals('solid', $properties ['border-bottom-style']);
        $this->assertEquals('#999', $properties ['border-color']);
        $this->assertEquals('#999', $properties ['border-left-color']);
        $this->assertEquals('#999', $properties ['border-right-color']);
        $this->assertEquals('#999', $properties ['border-top-color']);
        $this->assertEquals('#999', $properties ['border-bottom-color']);
        $this->assertEquals('', $properties ['']);
        $this->assertEquals('1.5em', $properties ['margin-bottom']);
    }

    /**
     * Test more complicated CSS parsing with dw and wrap CSS.
     * Part 2.
     */
    public function test_dw_and_wrap_css_part2 () {
        $properties = array();

        $import = new helper_plugin_odt_cssimport ();
        $import->importFromFile (TMP_DIR . '/data/dw_css_without_extra_wrap.css');
        $import->getPropertiesForElement ($properties, 'div', 'dokuwiki wrap_help', 'print');

        // For debugging: this will write the parsed/imported CSS in the file
        // /tmp/dwtests-xxx.yyy/data/odt_parsed.css
        //$handle = fopen (TMP_DIR . '/data/odt_parsed.css', 'w');
        //fwrite ($handle, $import->rulesToString());
        //fclose ($handle);

        // Check properties
        $this->assertEquals(26, count($properties));
        $this->assertEquals('2px solid #999', $properties ['border']);
        $this->assertEquals('2px solid #999', $properties ['border-left']);
        $this->assertEquals('2px solid #999', $properties ['border-right']);
        $this->assertEquals('2px solid #999', $properties ['border-top']);
        $this->assertEquals('2px solid #999', $properties ['border-bottom']);
        $this->assertEquals('2px', $properties ['border-width']);
        $this->assertEquals('2px', $properties ['border-left-width']);
        $this->assertEquals('2px', $properties ['border-right-width']);
        $this->assertEquals('2px', $properties ['border-top-width']);
        $this->assertEquals('2px', $properties ['border-bottom-width']);
        $this->assertEquals('solid', $properties ['border-style']);
        $this->assertEquals('solid', $properties ['border-left-style']);
        $this->assertEquals('solid', $properties ['border-right-style']);
        $this->assertEquals('solid', $properties ['border-top-style']);
        $this->assertEquals('solid', $properties ['border-bottom-style']);
        $this->assertEquals('#999', $properties ['border-color']);
        $this->assertEquals('#999', $properties ['border-left-color']);
        $this->assertEquals('#999', $properties ['border-right-color']);
        $this->assertEquals('#999', $properties ['border-top-color']);
        $this->assertEquals('#999', $properties ['border-bottom-color']);
        $this->assertEquals('1em 1em .5em', $properties ['padding']);
        $this->assertEquals('1em', $properties ['padding-top']);
        $this->assertEquals('1em', $properties ['padding-right']);
        $this->assertEquals('1em', $properties ['padding-left']);
        $this->assertEquals('.5em', $properties ['padding-bottom']);
        $this->assertEquals('1.5em', $properties ['margin-bottom']);
    }

    /**
     * Test some more  wrap CSS.
     * Part 3.
     */
    public function test_wrap_css() {
        $properties = array();
        $css_code = '/*____________ help ____________*/
.dokuwiki .wrap_help { background-color: #dcc2ef; }
.dokuwiki .wrap__dark.wrap_help { background-color: #3c1757; }
.dokuwiki div.wrap_help { background-image: url(images/note/48/help.png); }
.dokuwiki span.wrap_help { background-image: url(images/note/16/help.png); }';

        $import = new helper_plugin_odt_cssimport ();
        $import->importFromString ($css_code);
        $import->getPropertiesForElement ($properties, 'div', 'dokuwiki wrap_help');

        // For debugging: this will write the parsed/imported CSS in the file
        // /tmp/dwtests-xxx.yyy/data/odt_parsed.css
        //$handle = fopen (TMP_DIR . '/data/odt_parsed.css', 'w');
        //fwrite ($handle, $import->rulesToString());
        //fclose ($handle);

        // We shouldn't get any properties
        $this->assertEquals(2, count($properties));
        $this->assertEquals('#dcc2ef', $properties ['background-color']);
        $this->assertEquals('url(images/note/48/help.png)', $properties ['background-image']);
    }

    /**
     * Test some more  wrap CSS.
     * Part 4.
     */
    public function test_wrap_css_part2() {
        $properties = array();
        $css_code = '@media screen {
/*____________ help ____________*/
.dokuwiki .wrap_help { background-color: #dcc2ef; }
.dokuwiki .wrap__dark.wrap_help { background-color: #3c1757; }
.dokuwiki div.wrap_help { background-image: url(images/note/48/help.png); }
.dokuwiki span.wrap_help { background-image: url(images/note/16/help.png); }
}
@media print {
/* boxes and notes with icons
********************************************************************/

.dokuwiki div.wrap_box,
.dokuwiki div.wrap_danger, .dokuwiki div.wrap_warning, .dokuwiki div.wrap_caution, .dokuwiki div.wrap_notice, .dokuwiki div.wrap_safety,
.dokuwiki div.wrap_info, .dokuwiki div.wrap_important, .dokuwiki div.wrap_alert, .dokuwiki div.wrap_tip, .dokuwiki div.wrap_help, .dokuwiki div.wrap_todo, .dokuwiki div.wrap_download {
    border: 2px solid #999;
    padding: 1em 1em .5em;
    margin-bottom: 1.5em;
}
.dokuwiki span.wrap_box,
.dokuwiki span.wrap_danger, .dokuwiki span.wrap_warning, .dokuwiki span.wrap_caution, .dokuwiki span.wrap_notice, .dokuwiki span.wrap_safety,
.dokuwiki span.wrap_info, .dokuwiki span.wrap_important, .dokuwiki span.wrap_alert, .dokuwiki span.wrap_tip, .dokuwiki span.wrap_help, .dokuwiki span.wrap_todo, .dokuwiki span.wrap_download {
    border: 1px solid #999;
    padding: 0 .3em;
}
}';

        $import = new helper_plugin_odt_cssimport ();
        $import->importFromString ($css_code);
        $import->getPropertiesForElement ($properties, 'span', 'dokuwiki wrap_help', 'print');

        // For debugging: this will write the parsed/imported CSS in the file
        // /tmp/dwtests-xxx.yyy/data/odt_parsed.css
        $handle = fopen (TMP_DIR . '/data/odt_parsed.css', 'w');
        fwrite ($handle, $import->rulesToString());
        fclose ($handle);

        // We shouldn't get any properties
        $this->assertEquals(25, count($properties));
        $this->assertEquals('1px solid #999', $properties ['border']);
        $this->assertEquals('1px solid #999', $properties ['border-left']);
        $this->assertEquals('1px solid #999', $properties ['border-right']);
        $this->assertEquals('1px solid #999', $properties ['border-top']);
        $this->assertEquals('1px solid #999', $properties ['border-bottom']);
        $this->assertEquals('1px', $properties ['border-width']);
        $this->assertEquals('1px', $properties ['border-left-width']);
        $this->assertEquals('1px', $properties ['border-right-width']);
        $this->assertEquals('1px', $properties ['border-top-width']);
        $this->assertEquals('1px', $properties ['border-bottom-width']);
        $this->assertEquals('solid', $properties ['border-style']);
        $this->assertEquals('solid', $properties ['border-left-style']);
        $this->assertEquals('solid', $properties ['border-right-style']);
        $this->assertEquals('solid', $properties ['border-top-style']);
        $this->assertEquals('solid', $properties ['border-bottom-style']);
        $this->assertEquals('#999', $properties ['border-color']);
        $this->assertEquals('#999', $properties ['border-left-color']);
        $this->assertEquals('#999', $properties ['border-right-color']);
        $this->assertEquals('#999', $properties ['border-top-color']);
        $this->assertEquals('#999', $properties ['border-bottom-color']);
        $this->assertEquals('0 .3em', $properties ['padding']);
        $this->assertEquals('0', $properties ['padding-top']);
        $this->assertEquals('.3em', $properties ['padding-right']);
        $this->assertEquals('.3em', $properties ['padding-left']);
        $this->assertEquals('0', $properties ['padding-bottom']);
    }
}
