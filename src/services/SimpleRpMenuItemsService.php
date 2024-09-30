<?php
/**
 * Simple RP Menu plugin for Craft CMS 3.x
 *
 * This is a simple menu to add Singles, Structures, Channels, Categories, Custom menus (with description), etc to your name menu for CRAFT CMS V3.x
 *
 * @link      https://github.com/rpqa99
 * @copyright Copyright (c) 2022 rpqa99
 */

namespace rpqa99\simplerpmenu\services;

use rpqa99\simplerpmenu\models\SimpleRpMenuItemsModel;
use rpqa99\simplerpmenu\SimpleRpMenu;
use rpqa99\simplerpmenu\records\SimpleRpMenuItemsRecord;

use Craft;
use craft\base\Component;
use craft\elements\Entry;
use craft\elements\Category;

/**
 * SimpleRpMenuItemsService Service
 *
 * All of your plugin’s business logic should go in services, including saving data,
 * retrieving data, etc. They provide APIs that your controllers, template variables,
 * and other plugins can interact with.
 *
 * https://craftcms.com/docs/plugins/services
 *
 * @author    rpqa99
 * @package   SimpleRpMenu
 * @since     1.0.0
 */
class SimpleRpMenuItemsService extends Component
{
    // Public Properties
    // =========================================================================

    public function getSectionsWithEntries($site_id) {
        $sections = $this->getSections($site_id);

        if ($sections) {
            foreach($sections as $handle => $values) {
                if (!empty($sections[$handle])) {
                    foreach ($values as $index => $value) {
                        $sections[$handle][$index]['entries'] = $this->getFirstEntriesBySection($value['handle'], $site_id);
                    }
                }
            }
        }

        return $sections;
    }

    public function getMenuItem($id) {
        $record = SimpleRpMenuItemsRecord::findOne([
            'id' => $id
        ]);
        return new SimpleRpMenuItemsModel($record->getAttributes());
    }

    public function saveMenuItem(SimpleRpMenuItemsModel $model) {
        $record = false;
        if (isset($model->id)) {
            $record = SimpleRpMenuItemsRecord::findOne( [
                'id' => $model->id
            ]);
        }

        if (!$record) {
            $record = new SimpleRpMenuItemsRecord();
        }

        $record->menu_id = $model->menu_id;
        $record->parent_id = $model->parent_id;
        $record->item_order = $model->item_order;
        $record->name = $model->name;
        $record->entry_id = $model->entry_id;
        $record->custom_url = $model->custom_url;
        $record->class = $model->class;
        $record->class_parent = $model->class_parent;
        $record->data_json = $model->data_json;
        $record->target = $model->target;
        $record->noLink = $model->noLink;
        $record->customShortContent = $model->customShortContent;
        $record->title = $model->title;

        $save = $record->save();
        if ( !$save ) {
            Craft::getLogger()->log( $record->getErrors(), LOG_ERR, 'rp-simple-menu' );
        }
        return $record->id;
    }

    public function deleteMenuItem($id) {
        $record = SimpleRpMenuItemsRecord::findOne([
            'id' => $id
        ]);

        if ($record) {
            if ($record->delete()) {
                return 1;
            };
        }
    }

    public function deleteItemsByMenuId($record) {
        $records = SimpleRpMenuItemsRecord::findAll([
            'menu_id' => $record->id,
        ]);

        foreach ($records as $record) {
            $record->delete();
        }
        return;
    }

    public function getMenuItems($menuId) {
        $arrMenuItems = [];

        $menuItems = SimpleRpMenuItemsRecord::find()
                        ->where(['menu_id' => $menuId])
                        ->orderBy('item_order')
                        ->all();

        foreach ($menuItems as $intKey => $objItem) {
            $arrMenuItems[$intKey]['id'] = $objItem->id;
            $arrMenuItems[$intKey]['menu_id'] = $objItem->menu_id;
            $arrMenuItems[$intKey]['parent_id'] = $objItem->parent_id;
            $arrMenuItems[$intKey]['item_order'] = $objItem->item_order;
            $arrMenuItems[$intKey]['name'] = $objItem->name;
            $arrMenuItems[$intKey]['entry_id'] = $objItem->entry_id;
            $arrMenuItems[$intKey]['custom_url'] = $objItem->custom_url;
            $arrMenuItems[$intKey]['class'] = $objItem->class;
            $arrMenuItems[$intKey]['class_parent'] = $objItem->class_parent;
            $arrMenuItems[$intKey]['data_json'] = $objItem->data_json;
            $arrMenuItems[$intKey]['target'] = $objItem->target;
            $arrMenuItems[$intKey]['noLink'] = $objItem->noLink;
            $arrMenuItems[$intKey]['customShortContent'] = $objItem->customShortContent;
            $arrMenuItems[$intKey]['title'] = $objItem->title;
        }

        if ($arrMenuItems) {
            return $this->sortMenuItemsByParents($arrMenuItems);
        }
        return $arrMenuItems;
    }

    public function getMenuItemsAdminMarkup($menuId) {
        $localHTML = '';
        $arrMenuItems = $this->getMenuItems($menuId);

        if ($arrMenuItems) {
            foreach ($arrMenuItems as $menuItem) {
                $localHTML .= $this->getItemAdminMarkup($menuItem);
            }
        }
        return $localHTML;
    }

    private function getSections($site_id) {
        $sections = [];

        $sections['single'] = (new \craft\db\Query())
            ->select(["name AS name", "handle AS handle"])
            ->from(['{{%sections}}'])
            ->leftJoin('{{%sections_sites}}', '{{%sections_sites}}.sectionId = {{%sections}}.id')
            ->where(['type' => 'single', 'dateDeleted'=>NULL, 'siteId' => $site_id])
            ->orderBy('name')
            ->all();

        $sections['structure'] = (new \craft\db\Query())
            ->select(["name AS name", "handle AS handle"])
            ->from(['{{%sections}}'])
            ->leftJoin('{{%sections_sites}}', '{{%sections_sites}}.sectionId = {{%sections}}.id')
            ->where(['type' => 'structure', 'dateDeleted'=>NULL, 'siteId' => $site_id])
            ->orderBy('name')
            ->all();

        $sections['channel'] = (new \craft\db\Query())
            ->select(["name AS name", "handle AS handle"])
            ->from(['{{%sections}}'])
            ->leftJoin('{{%sections_sites}}', '{{%sections_sites}}.sectionId = {{%sections}}.id')
            ->where(['type' => 'channel', 'dateDeleted'=>NULL, 'siteId' => $site_id])
            ->orderBy('name')
            ->all();

        return $sections;
    }

    private function getEntriesBySection($handle, $site_id) {
        return Entry::find()
                    ->section($handle)
                    ->siteId($site_id)
                    ->all();
    }

    private function getFirstEntriesBySection($handle, $site_id) {
        return Entry::find()
                    ->section($handle)
                    ->siteId($site_id)
                    ->one();
    }

    private function sortMenuItemsByParents($arrMenuItems) {
        $counter = 0;
        $arrMenuItemsSorted = [];

        if ($arrMenuItems) {
            foreach ($arrMenuItems as $menuItem) {
                if ($menuItem['parent_id'] == 0) {
                    $arrMenuItemsSorted[] = $menuItem;
                }
            }
        }

        if ($arrMenuItemsSorted) {
            foreach ($arrMenuItemsSorted as $menuItem){
                $arrMenuItemsSorted[$counter] = $this->addChildToParent($arrMenuItems,$menuItem);
                $counter++;
            }
        }
        return $arrMenuItemsSorted;
    }
    
    private function addChildToParent($arrMenuItems, $menuItem) {
        $parent_id = $menuItem['id'];

        if ($arrMenuItems) {
            foreach ($arrMenuItems as $menuSubItem) {
                if ($menuSubItem['parent_id'] == $parent_id) {
                    $menuSubItem = $this->addChildToParent($arrMenuItems,$menuSubItem);
                    $menuItem['children'][] = $menuSubItem;
                }
            }
        }
        return $menuItem;
    }

    private function getItemAdminMarkup($menuItem) {
        $localHTML = '';

        $entry = Entry::find()
            ->id($menuItem['entry_id'])
            ->one();

        if (!$entry) {
            $entry = Category::find()
                ->id($menuItem['entry_id'])
                ->one();
        }

        $localHTML .= '<li id="menu-item-' .$menuItem['id']. '">';
            $localHTML .= '<div>';
                $localHTML .= '<div class="item-heading">';
                    $localHTML .= '<span class="settings-toggle"></span>';
                    $localHTML .= '<span class="menu-title">' . $menuItem['name'] . '</span>';
                    $localHTML .= '<span class="delete-menu btn small" data-id="' .$menuItem['id']. '">Delete</span>';
                $localHTML .= '</div>';
                $localHTML .= '<div class="item-content">';
                $localHTML .= '<input type="hidden" name="item-id" value="' .$menuItem['id']. '" />';
                    if ($menuItem['custom_url'] == '') $localHTML .= '<input type="hidden" name="item-entry-id" value="' .$menuItem['entry_id']. '" />';
                    $localHTML .= '<div class="inner">';
                        $localHTML .= '<div class="row field">';
                            $localHTML .= '<div class="heading">';
                                $localHTML .= '<label>' . Craft::t('rp-simple-menu', 'Name') . ':</label>';
                            $localHTML .= '</div>';
                            $localHTML .= '<div class="input">';
                                $localHTML .= '<input class="text nicetext fullwidth" type="text" name="item-name" value="' .$menuItem['name']. '" />';
                            $localHTML .= '</div>';
                        $localHTML .= '</div>';
                        $localHTML .= '<div class="row field">';
                            $localHTML .= '<div class="heading">';
                                $localHTML .= '<label>' . Craft::t('rp-simple-menu', 'Without Link ?') . ':</label>';
                            $localHTML .= '</div>';
                            $localHTML .= '<div class="input">';
                                $localHTML .= '<select id="noLink-'.$menuItem['id'].'" class="text nicetext fullwidth noLink-menu" name="noLink">';
                                    $localHTML .= '<option value="0" '.(($menuItem['noLink']=='0') ? 'selected' : '') .' >No</option>';
                                    $localHTML .= '<option value="1" '.(($menuItem['noLink']=='1') ? 'selected' : '') .'>Yes</option>';
                                $localHTML .= '</select>';
                            $localHTML .= '</div>';
                        $localHTML .= '</div>';

                        // $localHTML .= '<div class="row field">';
                        //     $localHTML .= '<div class="heading">';
                        //         $localHTML .= '<label>' . Craft::t('rp-simple-menu', 'Display Short Content ?') . ':</label>';
                        //     $localHTML .= '</div>';
                        //     $localHTML .= '<div class="input">';
                        //         $localHTML .= '<select id="customShortContent-'.$menuItem['id'].'" class="text nicetext fullwidth" name="customShortContent">';
                        //             $localHTML .= '<option value="0" '.(($menuItem['hasShortDescp']=='0') ? 'selected' : '') .' >No</option>';
                        //             $localHTML .= '<option value="1" '.(($menuItem['hasShortDescp']=='1') ? 'selected' : '') .'>Yes</option>';
                        //         $localHTML .= '</select>';
                        //     $localHTML .= '</div>';
                        // $localHTML .= '</div>';

                        $localHTML .= '<div class="row field">';
                            $localHTML .= '<div class="heading">';
                                $localHTML .= '<label>' . Craft::t('rp-simple-menu', 'Title') . ':</label>';
                            $localHTML .= '</div>';
                            $localHTML .= '<div class="input">';
                                $localHTML .= '<input class="text nicetext fullwidth" type="text" name="title" value="' .$menuItem['title']. '" />';
                            $localHTML .= '</div>';
                        $localHTML .= '</div>';


                        $localHTML .= '<div class="row field">';
                            $localHTML .= '<div class="heading">';
                                $localHTML .= '<label>' . Craft::t('rp-simple-menu', 'Menu Custom Short Content') . ':</label>';
                            $localHTML .= '</div>';
                            $localHTML .= '<div class="input">';
                                $localHTML .= '<textarea class="text nicetext fullwidth" name="custom-short-content">' .$menuItem['customShortContent']. '</textarea>';
                            $localHTML .= '</div>';
                        $localHTML .= '</div>';

                        if ($menuItem['entry_id'] == '') {
                            $localHTML .= '<div class="row field">';
                                $localHTML .= '<div class="heading">';
                                    $localHTML .= '<label>' . Craft::t('rp-simple-menu', 'Custom URL') . ':</label>';
                                $localHTML .= '</div>';
                                $localHTML .= '<div class="input">';
                                    $localHTML .= '<input class="text nicetext fullwidth" type="text" name="custom-url" value="' .$menuItem['custom_url']. '" />';
                                $localHTML .= '</div>';
                            $localHTML .= '</div>';
                        }
                        $localHTML .= '<div class="row field">';
                            $localHTML .= '<div class="heading">';
                                $localHTML .= '<label>' . Craft::t('rp-simple-menu', 'Class') . ':</label>';
                            $localHTML .= '</div>';
                            $localHTML .= '<div class="input">';
                                $localHTML .= '<input class="text nicetext fullwidth" type="text" name="class" value="' .$menuItem['class']. '" />';
                            $localHTML .= '</div>';
                        $localHTML .= '</div>';
                        $localHTML .= '<div class="row field">';
                            $localHTML .= '<div class="heading">';
                                $localHTML .= '<label>' . Craft::t('rp-simple-menu', 'Class parent') . ':</label>';
                            $localHTML .= '</div>';
                            $localHTML .= '<div class="input">';
                                $localHTML .= '<input class="text nicetext fullwidth" type="text" name="class-parent" value="' .$menuItem['class_parent']. '" />';
                            $localHTML .= '</div>';
                        $localHTML .= '</div>';
                        $localHTML .= '<div class="row field">';
                            $localHTML .= '<div class="heading">';
                                $localHTML .= '<label>' . Craft::t('rp-simple-menu', 'Data JSON') . ':</label>';
                            $localHTML .= '</div>';
                            $localHTML .= '<div class="input">';
                                $localHTML .= '<textarea class="text nicetext fullwidth" name="data-json">' .$menuItem['data_json']. '</textarea>';
                            $localHTML .= '</div>';
                        $localHTML .= '</div>';

                        $localHTML .= '<div class="row field">';
                            $localHTML .= '<div class="heading">';
                                $localHTML .= '<label>' . Craft::t('rp-simple-menu', 'Target options') . ':</label>';
                            $localHTML .= '</div>';
                            $localHTML .= '<div class="input">';
                                $localHTML .= '<select id="target-'.$menuItem['id'].'" class="text nicetext fullwidth" name="target">';
                                    $localHTML .= '<option value="_self" '.(($menuItem['target']=='_self') ? 'selected' : '') .' >Open in same tab</option>';
                                    $localHTML .= '<option value="_blank" '.(($menuItem['target']=='_blank') ? 'selected' : '') .'>Open in new tab</option>';
                                $localHTML .= '</select>';
                            $localHTML .= '</div>';
                        $localHTML .= '</div>';

                        if ($menuItem['custom_url'] == '') {
                            $localHTML .= '<div class="row field">';
                                $localHTML .= '<div class="heading">';
                                    if ($entry) $localHTML .= '<label>' . Craft::t('rp-simple-menu', 'Original') . ':</label> <a href="' . $entry->url . '" target="_blank">' . $entry->title . '</a>';
                                $localHTML .= '</div>';
                            $localHTML .= '</div>';
                        }
                    $localHTML .= '</div>';
                $localHTML .= '</div>';
            $localHTML .= '</div>';
            if (isset($menuItem['children'])) {
                $localHTML .= '<ol>';
                    foreach ($menuItem['children'] as $child) {
                       $localHTML .= $this->getItemAdminMarkup($child); 
                    }
                $localHTML .= '</ol>';
            }
        $localHTML .= '</li>'; 

        return $localHTML;
    }
}
