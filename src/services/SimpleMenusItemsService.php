<?php
/**
 * Simple RP Menu plugin for Craft CMS 3.x
 *
 * This is a simple menu to add Singles, Structures, Channels, Categories, Custom menus (with description), etc to your name menu for CRAFT CMS V3.x
 *
 * @link      https://github.com/bedh-rp
 * @copyright Copyright (c) 2022 Bedh Prakash
 */

namespace remoteprogrammer\simplerpmenu\services;

use remoteprogrammer\simplerpmenu\SimpleRpMenu;

use Craft;
use craft\base\Component;

/**
 * SimpleMenusItemsService Service
 *
 * All of your plugin’s business logic should go in services, including saving data,
 * retrieving data, etc. They provide APIs that your controllers, template variables,
 * and other plugins can interact with.
 *
 * https://craftcms.com/docs/plugins/services
 *
 * @author    Bedh Prakash
 * @package   SimpleRpMenu
 * @since     1.0.0
 */
class SimpleMenusItemsService extends Component
{
    // Public Properties
    // =========================================================================

    /**
     * Id attribute
     *
     * @var int
     */
    public $id;

    /**
     * Menu_id attribute
     *
     * @var int
     */
    public $menu_id;

    /**
     * Parent_id attribute
     *
     * @var int
     */
    public $parent_id;

    /**
     * Item_order attribute
     *
     * @var int
     */
    public $item_order;

    /**
     * Name attribute
     *
     * @var string
     */
    public $name;

    /**
     * Entry_id attribute
     *
     * @var int
     */
    public $entry_id;

    /**
     * Custom_url attribute
     *
     * @var string
     */
    public $custom_url;

    /**
     * Class attribute
     *
     * @var string
     */
    public $class;

    /**
     * Class_parent attribute
     *
     * @var string
     */
    public $class_parent;

    /**
     * Data_json attribute
     *
     * @var string
     */
    public $data_json;

    /**
     * Target attribute
     *
     * @var string
     */
    public $target;

    public $dateCreated;

    public $dateUpdated;

    public $uid;
    
    public $noLink;

    public $customShortContent;

    /**
     * This function can literally be anything you want, and you can have as many service
     * functions as you want
     *
     * From any other plugin file, call it like this:
     *
     *     SimpleRpMenu::$plugin->simpleMenusItemsService->exampleService()
     *
     * @return mixed
     */
    public function rules()
    {
        return [
            [['id', 'menu_id', 'parent_id', 'item_order', 'entry_id'], 'integer'],
            [['noLink', 'customShortContent'], 'safe'],
            [['name', 'custom_url', 'class', 'class_parent', 'data_json', 'target'], 'string'],
            [['menu_id', 'parent_id', 'item_order', 'name'], 'required'],
        ];
    }
}
