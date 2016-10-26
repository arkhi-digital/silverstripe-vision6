<?php

Object::add_extension("Page_Controller", "Vision6Ext");
ShortcodeParser::get('default')->register("vision6_list", array("Vision6Ext", "ShortCodeVision6List"));