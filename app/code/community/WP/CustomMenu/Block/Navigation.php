<?php

class WP_CustomMenu_Block_Navigation extends Mage_Catalog_Block_Navigation
{
    const CUSTOM_BLOCK_TEMPLATE = "wp_custom_menu_%d";

    private $_productsCount     = null;
    private $_topMenu           = array();
    private $_popupMenu         = array();

    public function drawCustomMenuMobileItem($category, $level = 0, $last = false)
    {
        if (!$category->getIsActive()) return '';
        $html = array();
        $id = $category->getId();
        // --- Sub Categories ---
        $activeChildren = $this->_getActiveChildren($category, $level);
        // --- class for active category ---
        $active = ''; if ($this->isCategoryActive($category)) $active = ' act';
        $hasSubMenu = count($activeChildren);
        $catUrl = $this->getCategoryUrl($category);
        $html[] = '<li id="menu-mobile-' . $id . '" class="menu-mobile level0' . $active . '">';
        // --- Top Menu Item ---
        $name = $this->escapeHtml($category->getName());
        $html[] = '<a class="level' . $level . $active . '" href="' . $catUrl .'" title="' . $name . '">';
        if (Mage::getStoreConfig('custom_menu/general/non_breaking_space')) {
            $name = str_replace(' ', '&nbsp;', $name);
        }
        $html[] =  $name ;
        $html[] = '</a>';
        if ($hasSubMenu) {
            $html[] = '<span class="button" rel="submenu-mobile-' . $id . '" onclick="wpSubMenuToggle(this, \'menu-mobile-' . $id . '\', \'submenu-mobile-' . $id . '\');">&nbsp;</span>';
        }
        // --- Add Popup block (hidden) ---
        if ($hasSubMenu) {
            // --- draw Sub Categories ---
            $html[] = '<ul id="submenu-mobile-' . $id . '" rel="level' . $level . '" class="wp-custom-menu-submenu" style="display: none;">';
            $html[] = $this->drawMobileMenuItem($activeChildren);
            $html[] = '</ul>';
        }
        $html[] = '</li>';
        $html = implode("\n", $html);
        return $html;
    }

    public function getTopMenuArray()
    {
        return $this->_topMenu;
    }

    public function getPopupMenuArray()
    {
        return $this->_popupMenu;
    }

    public function drawCustomMenuItem($category, $level = 0, $last = false)
    {
        if (!$category->getIsActive()) return;
        $htmlTop = array();
        $id = $category->getId();
        // --- Static Block ---
        $blockId = sprintf(self::CUSTOM_BLOCK_TEMPLATE, $id); // --- static block key
        #Mage::log($blockId);
        $collection = Mage::getModel('cms/block')->getCollection()
            ->addFieldToFilter('identifier', array(array('like' => $blockId . '_w%'), array('eq' => $blockId)))
            ->addFieldToFilter('is_active', 1);
        $blockId = $collection->getFirstItem()->getIdentifier();
        #Mage::log($blockId);
        $blockHtml = Mage::app()->getLayout()->createBlock('cms/block')->setBlockId($blockId)->toHtml();
        // --- Sub Categories ---
        $activeChildren = $this->_getActiveChildren($category, $level);
        // --- class for active category ---
        $active = ''; if ($this->isCategoryActive($category)) $active = ' act';
        // --- Popup functions for show ---
        $drawPopup = ($blockHtml || count($activeChildren));
        if ($drawPopup) {
            $htmlTop[] = '<li id="menu' . $id . '" class="menu' . $active . '" onmouseover="wpShowMenuPopup(this, event, \'popup' . $id . '\');" onmouseout="wpHideMenuPopup(this, event, \'popup' . $id . '\', \'menu' . $id . '\')">';
        } else {
            $htmlTop[] = '<li id="menu' . $id . '" class="menu' . $active . '">';
        }
        // --- Top Menu Item ---
        if ($level == 0 && $drawPopup) {
            $htmlTop[] = '<a  class="level' . $level . $active . '" href="'.$this->getCategoryUrl($category).'" title="'.$this->escapeHtml($category->getName()).'">';
        } else {
            $htmlTop[] = '<a  class="level' . $level . $active . '" href="'.$this->getCategoryUrl($category).'" title="'.$this->escapeHtml($category->getName()).'">';
        }
        $name = $this->escapeHtml($category->getName());
        if (Mage::getStoreConfig('custom_menu/general/non_breaking_space')) {
            $name = str_replace(' ', '&nbsp;', $name);
        }
        $htmlTop[] =  $name;
        $htmlTop[] = '</a>';
	   
	   
	   
	    if ($drawPopup) {
            $htmlPopup = array();
            // --- Popup function for hide ---
            $htmlTop[] = '<ul id="popup' . $id . '" class="wp-custom-menu-popup" onmouseout="wpHideMenuPopup(this, event, \'popup' . $id . '\', \'menu' . $id . '\')" onmouseover="wpPopupOver(this, event, \'popup' . $id . '\', \'menu' . $id . '\')">';
            // --- draw Sub Categories ---
            if (count($activeChildren)) {
                $columns = (int)Mage::getStoreConfig('custom_menu/columns/count');
                $htmlTop[] = $this->drawColumns($activeChildren, $columns);
            }
            // --- draw Custom User Block ---
            if ($blockHtml) {
                $htmlPopup[] = '<li id="' . $blockId . '" class="block2">';
                $htmlTop[] = $blockHtml;
                $htmlPopup[] = '</li>';
            }
            $htmlTop[] = '</ul>';
            $this->_popupMenu[] = implode("\n", $htmlTop);
        }
	   
        $htmlTop[] = '</li>';
        $this->_topMenu[] = implode("\n", $htmlTop);
        // --- Add Popup block (hidden) ---
       
    }

     public function drawMobileMenuItem($children, $level = 1)
    {
        $keyCurrent = $this->getCurrentCategory()->getId();
        $html = '';
        foreach ($children as $child) {
            if (is_object($child) && $child->getIsActive()) {
                // --- class for active category ---
                $active = '';
                $id = $child->getId();
                $activeChildren = $this->_getActiveChildren($child, $level);
                if ($this->isCategoryActive($child)) {
                    $active = ' actParent';
                    if ($id == $keyCurrent) $active = ' act';
                }
                $html.= '<li id="menu-mobile-' . $id . '" class="tt itemMenu level' . $level . $active . '">';
                // --- format category name ---
                $name = $this->escapeHtml($child->getName());
                if (Mage::getStoreConfig('custom_menu/general/non_breaking_space')) $name = str_replace(' ', '&nbsp;', $name);
                $html.= '<a class="itemMenuName level' . $level . $active . '" href="' . $this->getCategoryUrl($child) . '" title="' . $name . '"><span>' . $name . '</span></a>';
                if (count($activeChildren) > 0) {
                    $html.= '<span class="button" rel="submenu-mobile-' . $id . '" onclick="wpSubMenuToggle(this, \'menu-mobile-' . $id . '\', \'submenu-mobile-' . $id . '\');">&nbsp;</span>';
                }
                if (count($activeChildren) > 0) {
                    $html.= '<ul id="submenu-mobile-' . $id . '" rel="level' . $level . '" class="wp-custom-menu-submenu level' . $level . '" style="display: none;">';
                    $html.= $this->drawMobileMenuItem($activeChildren, $level + 1);
                    $html.= '</ul>';
                }
                $html.= '</li>';
            }
        }
        return $html;
    }


    public function drawMenuItem($children, $level = 1)
    {
        $html = '';
        $keyCurrent = $this->getCurrentCategory()->getId();
        foreach ($children as $child) {
            if (is_object($child) && $child->getIsActive()) {
                // --- class for active category ---
                $active = '';
                if ($this->isCategoryActive($child)) {
                    $active = ' actParent';
                    if ($child->getId() == $keyCurrent) $active = ' act';
                }
                // --- format category name ---
                $name = $this->escapeHtml($child->getName());
                if (Mage::getStoreConfig('custom_menu/general/non_breaking_space')) $name = str_replace(' ', '&nbsp;', $name);
			     $html.="<li id='menu".$child->getId()."' >"; 
                $html.= '<a class="itemMenuName level' . $level . $active . '" href="' . $this->getCategoryUrl($child) . '" title="' . $name . '">' . $name . '</a>';
				
                $activeChildren = $this->_getActiveChildren($child, $level);
                if (count($activeChildren) > 0) {
                    $html.= '<ul class="itemSubMenu level' . $level . '">';
                    $html.= $this->drawMenuItem($activeChildren, $level + 1);
			        $html.="</li>"; 
                    $html.= '</ul>';
                }else{
			        $html.="</li>"; 
                }
            }
        }
        return $html;
    }

    public function drawColumns($children, $columns = 1)
    {
        $html = '';
        // --- explode by columns ---
        if ($columns < 1) $columns = 1;
        $chunks = $this->_explodeByColumns($children, $columns);
        // --- draw columns ---
        $lastColumnNumber = count($chunks);
        $i = 1;
        foreach ($chunks as $key => $value) {
            if (!count($value)) continue;
            $class = '';
            if ($i == 1) $class.= ' first';
            if ($i == $lastColumnNumber) $class.= ' last';
            if ($i % 2) $class.= ' odd'; else $class.= ' even';
            $html.= $this->drawMenuItem($value, 1);
            $i++;
        }
        return $html;
    }

    protected function _getActiveChildren($parent, $level)
    {
        $activeChildren = array();
        // --- check level ---
        $maxLevel = (int)Mage::getStoreConfig('custom_menu/general/max_level');
        if ($maxLevel > 0) {
            if ($level >= ($maxLevel - 1)) return $activeChildren;
        }
        // --- / check level ---
        if (Mage::helper('catalog/category_flat')->isEnabled()) {
            $children = $parent->getChildrenNodes();
            $childrenCount = count($children);
        } else {
            $children = $parent->getChildren();
            $childrenCount = $children->count();
        }
        $hasChildren = $children && $childrenCount;
        if ($hasChildren) {
            foreach ($children as $child) {
                if ($this->_isCategoryDisplayed($child)) {
                    array_push($activeChildren, $child);
                }
            }
        }
        return $activeChildren;
    }

    private function _isCategoryDisplayed(&$child)
    {
        if (!$child->getIsActive()) return false;
        // === check products count ===
        // --- get collection info ---
        if (!Mage::getStoreConfig('custom_menu/general/display_empty_categories')) {
            $data = $this->_getProductsCountData();
            // --- check by id ---
            $id = $child->getId();
            #Mage::log($id); Mage::log($data);
            if (!isset($data[$id]) || !$data[$id]['product_count']) return false;
        }
        // === / check products count ===
        return true;
    }

    private function _getProductsCountData()
    {
        if (is_null($this->_productsCount)) {
            $collection = Mage::getModel('catalog/category')->getCollection();
            $storeId = Mage::app()->getStore()->getId();
            /* @var $collection Mage_Catalog_Model_Resource_Eav_Mysql4_Category_Collection */
            $collection->addAttributeToSelect('name')
                ->addAttributeToSelect('is_active')
                ->setStoreId($storeId);
			if(method_exists($collection, 'setProductStoreId'))
                $collection->setProductStoreId($storeId);
			if(method_exists($collection, 'setLoadProductCount'))
                $collection->setLoadProductCount(true);

            $productsCount = array();
            foreach($collection as $cat) {
                $productsCount[$cat->getId()] = array(
                    'name' => $cat->getName(),
                    'product_count' => $cat->getProductCount(),
                );
            }
            #Mage::log($productsCount);
            $this->_productsCount = $productsCount;
        }
        return $this->_productsCount;
    }

    private function _explodeByColumns($target, $num)
    {
        if ((int)Mage::getStoreConfig('custom_menu/columns/divided_horizontally')) {
            $target = self::_explodeArrayByColumnsHorisontal($target, $num);
        } else {
            $target = self::_explodeArrayByColumnsVertical($target, $num);
        }
        #return $target;
        if ((int)Mage::getStoreConfig('custom_menu/columns/integrate') && count($target)) {
            // --- combine consistently numerically small column ---
            // --- 1. calc length of each column ---
            $max = 0; $columnsLength = array();
            foreach ($target as $key => $child) {
                $count = 0;
                $this->_countChild($child, 1, $count);
                if ($max < $count) $max = $count;
                $columnsLength[$key] = $count;
            }
            // --- 2. merge small columns with next ---
            $xColumns = array(); $column = array(); $cnt = 0;
            $xColumnsLength = array(); $k = 0;
            foreach ($columnsLength as $key => $count) {
                $cnt+= $count;
                if ($cnt > $max && count($column)) {
                    $xColumns[$k] = $column;
                    $xColumnsLength[$k] = $cnt - $count;
                    $k++; $column = array(); $cnt = $count;
                }
                $column = array_merge($column, $target[$key]);
            }
            $xColumns[$k] = $column;
            $xColumnsLength[$k] = $cnt - $count;
            // --- 3. integrate columns of one element ---
            $target = $xColumns; $xColumns = array(); $nextKey = -1;
            if ($max > 1 && count($target) > 1) {
                foreach($target as $key => $column) {
                    if ($key == $nextKey) continue;
                    if ($xColumnsLength[$key] == 1) {
                        // --- merge with next column ---
                        $nextKey = $key + 1;
                        if (isset($target[$nextKey]) && count($target[$nextKey])) {
                            $xColumns[] = array_merge($column, $target[$nextKey]);
                            continue;
                        }
                    }
                    $xColumns[] = $column;
                }
                $target = $xColumns;
            }
        }
        $_rtl = Mage::getStoreConfigFlag('custom_menu/general/rtl');
        if ($_rtl) {
            $target = array_reverse($target);
        }
        return $target;
    }

    private function _countChild($children, $level, &$count)
    {
        foreach ($children as $child) {
            if ($child->getIsActive()) {
                $count++; $activeChildren = $this->_getActiveChildren($child, $level);
                if (count($activeChildren) > 0) $this->_countChild($activeChildren, $level + 1, $count);
            }
        }
    }

    private static function _explodeArrayByColumnsHorisontal($list, $num)
    {
        if ($num <= 0) return array($list);
        $partition = array();
        $partition = array_pad($partition, $num, array());
        $i = 0;
        foreach ($list as $key => $value) {
            $partition[$i][$key] = $value;
            if (++$i == $num) $i = 0;
        }
        return $partition;
    }

    private static function _explodeArrayByColumnsVertical($list, $num)
    {
        if ($num <= 0) return array($list);
        $listlen = count($list);
        $partlen = floor($listlen / $num);
        $partrem = $listlen % $num;
        $partition = array();
        $mark = 0;
        for ($column = 0; $column < $num; $column++) {
            $incr = ($column < $partrem) ? $partlen + 1 : $partlen;
            $partition[$column] = array_slice($list, $mark, $incr);
            $mark += $incr;
        }
        return $partition;
    }

    /**
     * Get url for category data
     *
     * @param Mage_Catalog_Model_Category $category
     * @return string
     */
    public function getCategoryUrl($category)
    {
        //Set URL to external one
        $externalUrl = $category['external_url'];
        if ($externalUrl) {
            return $externalUrl[0] === '/'
                ? Mage::app()->getStore()->getBaseUrl() . substr($externalUrl, 1)
                : $externalUrl;

        return parent::getCategoryUrl($category);
      }
    }
}
