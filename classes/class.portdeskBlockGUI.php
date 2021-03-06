<?php
require_once('./Services/Block/classes/class.ilBlockGUI.php');
require_once('./Modules/Portfolio/classes/class.ilPortfolioAccessHandler.php');
require_once('./Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php');
require_once('./Services/User/classes/class.ilObjUser.php');
require_once('./Modules/Portfolio/classes/class.ilObjPortfolio.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/PortfolioDesktop/classes/Portfolio/class.portdeskListGUI.php');

/**
 * Class portdeskBlockGUI
 *
 * @author            Fabian Schmid <fs@studer-raimann.ch>
 * @version           1.0.0
 *
 * @ilCtrl_IsCalledBy portdeskBlockGUI: ilColumnGUI, ilPersonalDesktopGUI
 * @ilCtrl_Calls      portdeskBlockGUI: ilCommonActionDispatcherGUI
 */
class portdeskBlockGUI extends ilBlockGUI {

	/**
	 * @var array
	 */
	protected static $prtf_path = array( 'ilPortfolioRepositoryGUI', 'ilobjportfoliogui' );
	/**
	 * @var string
	 */
	protected static $block_type = 'port_desk';


	public function __construct() {
		global $ilCtrl, $tpl;
		/**
		 * @var $ilCtrl ilCtrl
		 * @var $tpl    ilTemplate
		 */
		$this->ctrl = $ilCtrl;
		$this->pl = ilPortfolioDesktopPlugin::getInstance();
		$tpl->addCss($this->pl->getDirectory() . '/templates/port_desk.css');
	}


	/**
	 * @return string
	 */
	static function getBlockType() {
		return self::$block_type;
	}


	/**
	 * @return bool
	 */
	static function isRepositoryObject() {
		return false;
	}


	/**
	 * @var ilPortfolioAccessHandler
	 */
	protected static $handler;


	/**
	 * @return ilPortfolioAccessHandler
	 */
	protected static function getHandler() {
		if (!isset(self::$handler)) {
			self::$handler = new ilPortfolioAccessHandler();
		}

		return self::$handler;
	}


	/**
	 * @param int $id
	 *
	 * @return string
	 */
	protected function getSharedTargets($id) {
		$return = array();
		$handler = self::getHandler();
		foreach ($handler->getPermissions($id) as $obj_id) {
			switch ($obj_id) {
				case ilWorkspaceAccessGUI::PERMISSION_REGISTERED:
					$return[] = $this->pl->txt('shared_with_ilias');
					break;

				case ilWorkspaceAccessGUI::PERMISSION_ALL_PASSWORD:
					$return[] = $this->pl->txt('shared_with_www_pwd');
					break;

				case ilWorkspaceAccessGUI::PERMISSION_ALL:
					$return[] = $this->pl->txt('shared_with_www');
					break;

				default:
					$type = ilObject::_lookupType($obj_id);
					$return[] = $this->pl->txt('shared_with_' . $type);

					break;
			}
		}

		return implode(', ', array_unique($return));
	}


	/**
	 * @param $a_set
	 */
	public function fillRow($a_set) {
		global $ilUser;

		$this->ctrl->setParameterByClass('ilobjportfoliogui', 'prt_id', $a_set['id']);
		$preview_link = $this->ctrl->getLinkTargetByClass(self::$prtf_path, 'preview');

		$this->tpl->touchBlock('d_1');
		if ($a_set['is_default'] == 1) {
			$this->tpl->setVariable('SRC_ICON', ilObjUser::_getPersonalPicturePath($ilUser->getId()));
		} else {
			if ((int)str_ireplace('.', '', ILIAS_VERSION_NUMERIC) >= 500) {

				$this->tpl->setVariable('SRC_ICON', ilUtil::getImagePath('icon_prtt.svg'));
			} else {
				$this->tpl->setVariable('SRC_ICON', ilUtil::getImagePath('icon_prtt.png'));
			}
		}

		$this->tpl->setVariable('DIV_CLASS', 'ilContainerListItemOuter');
		$this->tpl->setVariable('TXT_TITLE_LINKED', $a_set['title']);
		$this->tpl->setVariable('HREF_TITLE_LINKED', $preview_link);

		$shared = $this->getSharedTargets($a_set['id']);
		if ($shared) {

			$this->tpl->setVariable('TXT_PROP', $this->pl->txt('alt_shared'));
			$this->tpl->setVariable('VAL_PROP', $shared);
			//						$this->tpl->setVariable('TXT_SHARED', $this->pl->txt('alt_shared') . ': ' . $shared);
		}

		$current_selection_list = new ilAdvancedSelectionListGUI();
		$current_selection_list->setSelectionHeaderClass('small');
		$current_selection_list->setSelectionHeaderSpanClass('small');
		$current_selection_list->setListTitle($this->pl->txt('port_actions'));
		$current_selection_list->setId('port_id' . $a_set['id']);
		$current_selection_list->setUseImages(false);

		$current_selection_list->addItem($this->pl->txt('port_preview'), 'port_preview', $preview_link);
		$current_selection_list->addItem($this->pl->txt('port_edit'), 'port_edit', $this->ctrl->getLinkTargetByClass(self::$prtf_path, 'view'));

		$this->tpl->setVariable('COMMAND_SELECTION_LIST', $current_selection_list->getHTML());
	}


	public function getHTML() {
		global $ilUser;
		$this->setRowTemplate('tpl.container_list_item.html', $this->pl->getDirectory());
		$this->setData(ilObjPortfolio::getPortfoliosOfUser($ilUser->getId()));

		return parent::getHTML(); // TODO: Change the autogenerated stub
	}





	//	public function fillDataSection() {
	//		global $ilUser;
	//		//		$ilPortfolioAccessHandler = new ilPortfolioAccessHandler();
	//		$tpl = $this->pl->getTemplate('tpl.list_item.html');
	//		$prtf_path = array( 'ilPortfolioRepositoryGUI', 'ilobjportfoliogui' );
	//		foreach (ilObjPortfolio::getPortfoliosOfUser($ilUser->getId()) as $portfolio) {
	//
	////			$portdeskListGUI = new portdeskListGUI();
	//
	////			$refs = ilObject2::_getAllReferences($portfolio['id']);
	////						echo $portdeskListGUI->getListItemHTML($portfolio, 'lorem', 'iposum');
	////			var_dump($refs); // FSX
	//
	////			require_once('./Modules/Portfolio/classes/class.ilObjPortfolioTemplateListGUI.php');
	////			$ilObjPortfolioTemplateListGUI = new ilObjPortfolioTemplateListGUI();
	////			var_dump($ilObjPortfolioTemplateListGUI->getListItemHTML(0, $portfolio['id'], 'lorem', 'iposum')); // FSX
	//
	//			$this->ctrl->setParameterByClass('ilobjportfoliogui', 'prt_id', $portfolio['id']);
	//			$tpl->setCurrentBlock('item');
	//			$preview_link = $this->ctrl->getLinkTargetByClass($prtf_path, 'preview');
	//
	//			$tpl->setVariable('TITLE_LINK', $portfolio['title']);
	//			$tpl->setVariable('ITEM_LINK', $preview_link);
	//			if ($portfolio['is_default'] == 1) {
	//				$tpl->setVariable('IMG_SRC', ilObjUser::_getPersonalPicturePath($ilUser->getId()));
	//				//				$tpl->setVariable('IMG_SRC', '/Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/PortfolioDesktop/templates/img/no_photo.png');
	//			}
	//
	//			$shared = $this->getSharedTargets($portfolio['id']);
	//			if ($shared) {
	//				$tpl->setVariable('TXT_SHARED', $this->pl->txt('alt_shared') . ': ' . $shared);
	//			}
	//
	//			$current_selection_list = new ilAdvancedSelectionListGUI();
	//			$current_selection_list->setSelectionHeaderClass('small');
	//			$current_selection_list->setSelectionHeaderSpanClass('small');
	//			$current_selection_list->setListTitle($this->pl->txt('port_actions'));
	//			$current_selection_list->setId('port_id' . $portfolio['id']);
	//			$current_selection_list->setUseImages(false);
	//
	//			$current_selection_list->addItem($this->pl->txt('port_preview'), 'port_preview', $preview_link);
	//			$current_selection_list->addItem($this->pl->txt('port_edit'), 'port_edit', $this->ctrl->getLinkTargetByClass($prtf_path, 'view'));
	//
	//			$tpl->setVariable('ACTIONS', $current_selection_list->getHTML());
	//			$tpl->parseCurrentBlock();
	//		}
	////		$this->setRowTemplate()
	//		$this->setDataSection($tpl->get());
	//	}
}

?>