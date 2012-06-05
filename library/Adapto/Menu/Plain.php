<?PHP
/**
 * This file is part of the Adapto Toolkit.
 * Detailed copyright and licensing information can be found
 * in the doc/COPYRIGHT and doc/LICENSE files which should be
 * included in the distribution.
 *
 * @package adapto
 * @subpackage menu
 *
 * @copyright (c)2000-2004 Ibuildings.nl BV
 * @license http://www.achievo.org/atk/licensing ATK Open Source License
 *

 */

/**
 * Implementation of the plaintext menu.
 *
 * @author Ber Dohmen <ber@ibuildings.nl>
 * @author Sandy Pleyte <sandy@ibuildings.nl>
 * @package adapto
 * @subpackage menu
 */
class Adapto_Menu_Plain extends Adapto_menuinterface
{
    public $m_height; // defaulted to public

    /**
     * Constructor
     *
     * @return Adapto_Menu_Plain
     */

    public function __construct()
    {
        $this->m_height = "50";
    }

    /**
     * Render the menu
     * @return String HTML fragment containing the menu.
     */
    function render()
    {
        $page = Adapto_ClassLoader::getInstance("atk.ui.atkpage");
        $theme = Adapto_ClassLoader::getInstance("Adapto_Ui_Theme");
        $page->addContent($this->getMenu());

        return $page->render("Menu", true);
    }

    /**
     * Get the menu
     *
     * @return string The menu
     */
    function getMenu()
    {
        global $Adapto_VARS, $g_menu, $g_menu_parent;
        $atkmenutop = atkArrayNvl($Adapto_VARS, "atkmenutop", "main");
        $theme = Adapto_ClassLoader::getInstance('Adapto_Ui_Theme');
        $page = Adapto_ClassLoader::getInstance('atk.atkpage');

        $menu = $this->getHeader($atkmenutop);
        if (is_array($g_menu[$atkmenutop])) {
            usort($g_menu[$atkmenutop], array("atkplainmenu", "menu_cmp"));
            $menuitems = array();
            for ($i = 0; $i < count($g_menu[$atkmenutop]); $i++) {
                if ($i == count($g_menu[$atkmenutop]) - 1) {
                    $delimiter = "";
                } else {
                    $delimiter = Adapto_Config::getGlobal("menu_delimiter");
                }
                $name = $g_menu[$atkmenutop][$i]["name"];
                $menuitems[$i]["name"] = $name;
                $url = $g_menu[$atkmenutop][$i]["url"];
                $enable = $this->isEnabled($g_menu[$atkmenutop][$i]);
                $modname = $g_menu[$atkmenutop][$i]["module"];

                $menuitems[$i]["enable"] = $enable;

                /* delimiter ? */
                if ($name == "-")
                    $menu .= $delimiter;

                /* submenu ? */
                else if (empty($url) && $enable) {
                    $url = $theme->getAttribute('menufile', Adapto_Config::getGlobal("menufile", 'menu.php')) . '?atkmenutop=' . $name;
                    $menu .= href($url, $this->getMenuTranslation($name, $modname), SESSION_DEFAULT) . $delimiter;
                } else if (empty($url) && !$enable) {
                    //$menu .=text("menu_$name").$config_menu_delimiter;
                } /* normal menu item */
 else if ($enable)
                    $menu .= href($url, $this->getMenuTranslation($name, $modname), SESSION_NEW, false,
                            $theme->getAttribute('menu_params', Adapto_Config::getGlobal('menu_params', 'target="main"'))) . $delimiter;
                else {
                    //$menu .= text("menu_$name").$config_menu_delimiter;
                }
                $menuitems[$i]["url"] = session_url($url);
            }
        }
        /* previous */
        if ($atkmenutop != "main") {
            $parent = $g_menu_parent[$atkmenutop];
            $menu .= Adapto_Config::getGlobal("menu_delimiter");
            $menu .= href($theme->getAttribute('menufile', Adapto_Config::getGlobal("menufile", 'menu.php')) . '?atkmenutop=' . $parent,
                    atktext("back_to", "atk") . ' ' . $this->getMenuTranslation($parent, $modname), SESSION_DEFAULT) . $delimiter;
        }
        $menu .= $this->getFooter($atkmenutop);
        $page->register_style($theme->stylePath("style.css"));
        $page->register_script(Adapto_Config::getGlobal("atkroot") . "atk/javascript/menuload.js");
        $ui = Adapto_ClassLoader::getInstance("atk.ui.atkui");

        return $ui
                ->renderBox(
                        array("title" => $this->getMenuTranslation($atkmenutop, $modname), "content" => $menu, "menuitems" => $menuitems), "menu");
    }

    /**
     * Compare two menuitems
     *
     * @param array $a
     * @param array $b
     * @return int
     */
    function menu_cmp($a, $b)
    {
        if ($a["order"] == $b["order"])
            return 0;
        return ($a["order"] < $b["order"]) ? -1 : 1;
    }

    /**
     * Get the height for this menu
     *
     * @return int The height of the menu
     */
    function getHeight()
    {
        return $this->m_height;
    }

    /**
     * Get the menu position
     *
     * @return int The menu position (MENU_RIGHT, MENU_TOP, MENU_BOTTOM or MENU_LEFT)
     */
    function getPosition()
    {
        switch (Adapto_Config::getGlobal("menu_pos", "left")) {
        case "right":
            return MENU_RIGHT;
        case "top":
            return MENU_TOP;
        case "bottom":
            return MENU_BOTTOM;
        }
        return MENU_LEFT;
    }

    /**
     * Is this menu scrollable?
     *
     * @return int MENU_SCROLLABLE or MENU_UNSCROLLABLE 
     */
    function getScrollable()
    {
        return MENU_SCROLLABLE;
    }

    /**
     * Is this menu multilevel?
     *
     * @return int MENU_MULTILEVEL or MENU_NOMULTILEVEL
     */
    function getMultilevel()
    {
        return MENU_MULTILEVEL;
    }
}

?>