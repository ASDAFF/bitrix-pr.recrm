<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
/** @var CBitrixComponent $this */
/** @var array $arParams */
/** @var array $arResult */
/** @var string $componentPath */
/** @var string $componentName */
/** @var string $componentTemplate */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

/* Add JQuery */
CJSCore::Init(array("jquery"));

/* ReCrm */
if(!CModule::IncludeModule("pr.recrm")):
	ShowError(GetMessage("PR_RECRM_C_ERR_MODULE"));
	return;
endif;

$RECRM = new prReCrmData;

/* IB Type */
$arParams["IBLOCK_TYPE"] = trim($arParams["IBLOCK_TYPE"]);
if(strlen($arParams["IBLOCK_TYPE"]) <= 0 OR $arParams["IBLOCK_TYPE"] == "-"):
	ShowError(GetMessage("PR_RECRM_C_ERR_IBT"));
	return;
endif;

/* IB ID */
$arParams["IBLOCK_ID"] = trim($arParams["IBLOCK_ID"]);
if(strlen($arParams["IBLOCK_ID"]) <= 0):
	ShowError(GetMessage("PR_RECRM_C_ERR_IB"));
	return;
endif;

/* No Session */
CPageOption::SetOptionString("main", "nav_page_in_session", "N");

/* Cache Time */
if(!isset($arParams["CACHE_TIME"]))
	$arParams["CACHE_TIME"] = 36000000;

/* Check El Id */
$arParams["ELEMENT_ID"] 	= intval($arParams["~ELEMENT_ID"]);
if($arParams["ELEMENT_ID"] > 0 && $arParams["ELEMENT_ID"]."" != $arParams["~ELEMENT_ID"]):
	ShowError(GetMessage("PR_RECRM_C_ERR_404"));
	@define("ERROR_404", "Y");
	if($arParams["SET_STATUS_404"]==="Y")
		CHTTP::SetStatus("404 Not Found");
	return;
endif;

/* Props */
$ADD_PROPS_COMP = array();
if(is_array($arParams["DETAIL_PROPS"]) AND count($arParams["DETAIL_PROPS"]) > 0):
	$arParams["DETAIL_PROPS"] = array_filter($arParams["DETAIL_PROPS"]);
else:
	$arParams["DETAIL_PROPS"] = array();
endif;

/* Photos Prop */
if($arParams["DETAIL_PHOTOS"] == "Y"):
	if(!in_array("estatephoto", $arParams["DETAIL_PROPS"])):
		$arParams["DETAIL_PROPS"][] = "estatephoto";
		$ADD_PROPS_COMP[] = 'estatephoto';
	endif;
	
	if(!in_array("estatephotolayout", $arParams["DETAIL_PROPS"])):
		$arParams["DETAIL_PROPS"][] = "estatephotolayout";
		$ADD_PROPS_COMP[] = 'estatephotolayout';
	endif;
endif;

/* Map Prop */
if($arParams["DETAIL_MAP"] == "Y"):
	if(!in_array("zoom", $arParams["DETAIL_PROPS"])):
		$arParams["DETAIL_PROPS"][] = "zoom";
		$ADD_PROPS_COMP[] = 'zoom';
	endif;
	
	if(!in_array("latitude", $arParams["DETAIL_PROPS"])):
		$arParams["DETAIL_PROPS"][] = "latitude";
		$ADD_PROPS_COMP[] = 'latitude';
	endif;

	if(!in_array("longitude", $arParams["DETAIL_PROPS"])):
		$arParams["DETAIL_PROPS"][] = "longitude";
		$ADD_PROPS_COMP[] = 'longitude';
	endif;
endif;

/* Video Prop */
if($arParams["DETAIL_VIDEO"] == "Y" AND !in_array("youtube_url", $arParams["DETAIL_PROPS"])):
	$arParams["DETAIL_PROPS"][] = "youtube_url";
	$ADD_PROPS_COMP[] = 'youtube_url';
endif;

/* Agent Prop */
if($arParams["DETAIL_AGENT"] == "Y" AND !in_array("agent_id", $arParams["DETAIL_PROPS"])):
	$arParams["DETAIL_PROPS"][] = "agent_id";
	$ADD_PROPS_COMP[] = 'agent_id';
endif;

/* Desc */
$arParams["DETAIL_TEXT"] = $arParams["DETAIL_TEXT"] == "Y";

/* IB Url */
$arParams["IBLOCK_URL"] = trim($arParams["IBLOCK_URL"]);

/* Additional */
$arParams["META_KEYWORDS"] = trim($arParams["META_KEYWORDS"]);
if(strlen($arParams["META_KEYWORDS"]) <= 0)
	$arParams["META_KEYWORDS"] = "-";

$arParams["META_DESCRIPTION"] = trim($arParams["META_DESCRIPTION"]);
if(strlen($arParams["META_DESCRIPTION"]) <= 0)
	$arParams["META_DESCRIPTION"] = "-";

$arParams["BROWSER_TITLE"] = trim($arParams["BROWSER_TITLE"]);
if(strlen($arParams["BROWSER_TITLE"]) <= 0)
	$arParams["BROWSER_TITLE"] = "-";

$arParams["INCLUDE_IBLOCK_INTO_CHAIN"]	= $arParams["INCLUDE_IBLOCK_INTO_CHAIN"]!="N";
$arParams["ADD_ELEMENT_CHAIN"]			= (isset($arParams["ADD_ELEMENT_CHAIN"]) && $arParams["ADD_ELEMENT_CHAIN"] == "Y");
$arParams["SET_TITLE"]					= $arParams["SET_TITLE"]!="N";
$arParams["SET_BROWSER_TITLE"]			= (isset($arParams["SET_BROWSER_TITLE"]) && $arParams["SET_BROWSER_TITLE"] === 'N' ? 'N' : 'Y');
$arParams["SET_META_KEYWORDS"]			= (isset($arParams["SET_META_KEYWORDS"]) && $arParams["SET_META_KEYWORDS"] === 'N' ? 'N' : 'Y');
$arParams["SET_META_DESCRIPTION"]		= (isset($arParams["SET_META_DESCRIPTION"]) && $arParams["SET_META_DESCRIPTION"] === 'N' ? 'N' : 'Y');

$arParams["DISPLAY_TOP_PAGER"]			= $arParams["DISPLAY_TOP_PAGER"]=="Y";
$arParams["DISPLAY_BOTTOM_PAGER"]		= $arParams["DISPLAY_BOTTOM_PAGER"]!="N";
$arParams["PAGER_TITLE"]				= trim($arParams["PAGER_TITLE"]);
$arParams["PAGER_SHOW_ALWAYS"]			= $arParams["PAGER_SHOW_ALWAYS"]!="N";
$arParams["PAGER_TEMPLATE"]				= trim($arParams["PAGER_TEMPLATE"]);
$arParams["PAGER_SHOW_ALL"]				= $arParams["PAGER_SHOW_ALL"]!=="N";

if($arParams["DISPLAY_TOP_PAGER"] || $arParams["DISPLAY_BOTTOM_PAGER"])
{
	$arNavParams = array(
		"nPageSize" => 1,
		"bShowAll" 	=> $arParams["PAGER_SHOW_ALL"],
	);
	$arNavigation = CDBResult::GetNavParams($arNavParams);
}
else
{
	$arNavigation = false;
}

$arParams["SHOW_WORKFLOW"] = $_REQUEST["show_workflow"]=="Y";

/* Access */
$arParams["USE_PERMISSIONS"] = $arParams["USE_PERMISSIONS"]=="Y";
if(!is_array($arParams["GROUP_PERMISSIONS"]))
	$arParams["GROUP_PERMISSIONS"] = array(1);

$bUSER_HAVE_ACCESS = !$arParams["USE_PERMISSIONS"];
if($arParams["USE_PERMISSIONS"] && isset($USER) && is_object($USER))
{
	$arUserGroupArray = $USER->GetUserGroupArray();
	foreach($arParams["GROUP_PERMISSIONS"] as $PERM)
	{
		if(in_array($PERM, $arUserGroupArray))
		{
			$bUSER_HAVE_ACCESS = true;
			break;
		}
	}
}
if(!$bUSER_HAVE_ACCESS)
{
	ShowError(GetMessage("PR_RECRM_C_ERR_ACCESS"));
	return 0;
}

$arParams["CHECK_DATES"] 			= false;
$arParams["FIELD_CODE"] 			= array();
$arParams["PROPERTY_CODE"] 			= array();

if($arParams["SHOW_WORKFLOW"] || $this->StartResultCache(false, array(($arParams["CACHE_GROUPS"]==="N"? false: $USER->GetGroups()),$bUSER_HAVE_ACCESS, $arNavigation)))
{

	if(!CModule::IncludeModule("iblock"))
	{
		$this->AbortResultCache();
		ShowError(GetMessage("PR_RECRM_C_ERR_MOD_IB"));
		return;
	}
	$arFilter = array(
		"IBLOCK_LID" 			=> SITE_ID,
		"IBLOCK_ACTIVE"			=> "Y",
		"ACTIVE"				=> "Y",
		"CHECK_PERMISSIONS"		=> "Y",
		"IBLOCK_TYPE"			=> $arParams["IBLOCK_TYPE"],
		"SHOW_HISTORY"			=> $arParams["SHOW_WORKFLOW"]? "Y": "N",
	);
	if(intval($arParams["IBLOCK_ID"]) > 0)
		$arFilter["IBLOCK_ID"] = $arParams["IBLOCK_ID"];

	if($arParams["ELEMENT_ID"] <= 0):
		$arParams["ELEMENT_ID"] = CIBlockFindTools::GetElementID(
			$arParams["ELEMENT_ID"],
			$arParams["ELEMENT_CODE"],
			false,
			false,
			$arFilter
		);
	endif;
	
	$arFilter["ID"] = $arParams["ELEMENT_ID"];

	$WF_SHOW_HISTORY = "N";
	if ($arParams["SHOW_WORKFLOW"] && CModule::IncludeModule("workflow"))
	{
		$WF_ELEMENT_ID = CIBlockElement::WF_GetLast($arParams["ELEMENT_ID"]);

		$WF_STATUS_ID = CIBlockElement::WF_GetCurrentStatus($WF_ELEMENT_ID, $WF_STATUS_TITLE);
		$WF_STATUS_PERMISSION = CIBlockElement::WF_GetStatusPermission($WF_STATUS_ID);

		if ($WF_STATUS_ID == 1 || $WF_STATUS_PERMISSION < 1)
			$WF_ELEMENT_ID = $arParams["ELEMENT_ID"];
		else
			$WF_SHOW_HISTORY = "Y";

		$arParams["ELEMENT_ID"] = $WF_ELEMENT_ID;
	}

	$arFilter["SHOW_HISTORY"] = $WF_SHOW_HISTORY;

	$arSelect = array(
		"ID",
		"NAME",
		"IBLOCK_ID",
		"IBLOCK_SECTION_ID",
		"DETAIL_TEXT",
		"DETAIL_TEXT_TYPE",
		"PREVIEW_TEXT",
		"PREVIEW_TEXT_TYPE",
		"DETAIL_PICTURE",
		"ACTIVE_FROM",
		"LIST_PAGE_URL",
		"DETAIL_PAGE_URL",
	);
	
	/*
	if($arParams["DETAIL_PROPS_EMPTY"] == "Y"):
		$arFilterProps = array();
	else:
		$arFilterProps = array("EMPTY" => "N");
	endif;
	*/

	$rsElement = CIBlockElement::GetList(array(), $arFilter, false, false, $arSelect);
	$rsElement->SetUrlTemplates($arParams["DETAIL_URL"], "", $arParams["IBLOCK_URL"]);
	if($obElement = $rsElement->GetNextElement())
	{
		$arItem 	= $obElement->GetFields();
		$arProps 	= $obElement->GetProperties(array("SORT"=>"ASC")); //, $arFilterProps);
		
		$arResult["PROPERTIES"] = $arProps;

		$arResult["NAV_RESULT"] = new CDBResult;
		if(($arResult["DETAIL_TEXT_TYPE"] == "html") AND (strstr($arResult["DETAIL_TEXT"], "<BREAK />") !== false)):
			$arPages = explode("<BREAK />", $arResult["DETAIL_TEXT"]);
		elseif(($arResult["DETAIL_TEXT_TYPE"] != "html") AND (strstr($arResult["DETAIL_TEXT"], "&lt;BREAK /&gt;") !== false)):
			$arPages = explode("&lt;BREAK /&gt;", $arResult["DETAIL_TEXT"]);
		else:
			$arPages = array();
		endif;

		$arResult["NAV_RESULT"]->InitFromArray($arPages);
		$arResult["NAV_RESULT"]->NavStart($arNavParams);
		if(count($arPages) == 0):
			$arResult["NAV_RESULT"] = false;
		else:
			$arResult["NAV_STRING"] = $arResult["NAV_RESULT"]->GetPageNavStringEx($navComponentObject, $arParams["PAGER_TITLE"], $arParams["PAGER_TEMPLATE"], $arParams["PAGER_SHOW_ALWAYS"]);
			$arResult["NAV_CACHED_DATA"] = $navComponentObject->GetTemplateCachedData();

			$arResult["NAV_TEXT"] = "";
			while($ar = $arResult["NAV_RESULT"]->Fetch())
				$arResult["NAV_TEXT"] .= $ar;
		endif;

		$ipropValues = new \Bitrix\Iblock\InheritedProperty\ElementValues($arResult["IBLOCK_ID"], $arResult["ID"]);
		$arResult["IPROPERTY_VALUES"] = $ipropValues->getValues();

		/* Photos */
		$arItem['PHOTOS_URL'] = array();
		if($arParams["DETAIL_PHOTOS"] == "Y"):
			if(!is_array($arProps['estatephoto']['VALUE']))
				$arProps['estatephoto']['VALUE'] = array();
			
			if(!is_array($arProps['estatephotolayout']['VALUE']))
				$arProps['estatephotolayout']['VALUE'] = array();

			$arItem['PHOTOS_URL'] = array_merge($arProps['estatephoto']['VALUE'], $arProps['estatephotolayout']['VALUE']);
		endif;

		/* Map */
		$arItem["MAP"] = array();
		if($arParams["DETAIL_MAP"] == "Y"):			
			if(intval($arProps['longitude']['VALUE']) > 0):
				$arItem["MAP"][] = array(
					'ID' => $arItem['ID'],
					'LOC' => array($arProps['latitude']['VALUE'], $arProps['longitude']['VALUE']),
				);
			endif;
		endif;

		/* Video */
		$arItem['VIDEO_URL'] = false;
		if($arParams["DETAIL_VIDEO"] == "Y"):
			if (preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $arProps['youtube_url']['VALUE'], $youtube_match)):
				$arItem['VIDEO_URL'] = 'http://www.youtube.com/embed/'.$youtube_match[1].'?enablejsapi=1&wmode=opaque';
			endif;
		endif;
		
		/* Agent */
		$arItem['AGENT'] = false;
		if($arParams["DETAIL_AGENT"] == "Y"):
			if(intval($arProps['agent_id']['VALUE']) > 0):

				$agentRes = CIBlockElement::GetList(
					array(), 
					array(
						"IBLOCK_ID" 	=> $RECRM->getIBId('agent'),
						"PROPERTY_id" 	=> intval($arProps['agent_id']['VALUE'])
					), 
					false, 
					false, 
					array(
						"ID", 
						"NAME",
						"PROPERTY_email",
						"PROPERTY_mobile_phone",
						"PROPERTY_phone",
						"PROPERTY_photo",
					)
				);
				while($agentOb = $agentRes->GetNextElement())
				{
					$arAgentF = $agentOb->GetFields();
					$arItem['AGENT']['NAME'] = $arAgentF["NAME"];
					$arItem['AGENT']['MOBILE'] = $arAgentF["PROPERTY_MOBILE_PHONE_VALUE"];
					$arItem['AGENT']['PHONE'] = $arAgentF["PROPERTY_PHONE_VALUE"];
					$arItem['AGENT']['EMAIL'] = $arAgentF["PROPERTY_EMAIL_VALUE"];
					$arItem['AGENT']['PHOTO'] = $arAgentF["PROPERTY_PHOTO_VALUE"];
				}
			endif;
		endif;
		
		
		/* Remove Prop for Data*/
		if(count($ADD_PROPS_COMP) > 0):
			foreach($ADD_PROPS_COMP AS $PROP_U):
				unset($arProps[$PROP_U]);
			endforeach;
		endif;

		/* Props */
		$arItem['PROPERTIES_RECRM'] = array();
		foreach($arProps AS $prop)
		{
			if(in_array($prop['CODE'], $arParams["DETAIL_PROPS"])):
				
				if($arParams["DETAIL_PROPS_EMPTY"] != "Y" AND $prop['VALUE'] == '')
					continue;
				
				/* ����������� ������ ��� ����������� */

				/* boll */
				if($prop['VALUE'] === 'true')
					$prop['VALUE'] = '��';
				if($prop['VALUE'] === 'false')
					$prop['VALUE'] = '���';
				
				/* arr */
				if(is_array($prop['VALUE']))
					$prop['VALUE'] = implode('<br>', $prop['VALUE']);
				
				/* date */
				if(in_array($prop['CODE'], array('creation_date', 'edit_date')))
					$prop['VALUE'] = date(GetMessage("PR_RECRM_C_DATE"), $prop['VALUE']);

				/* datetime */
				if(in_array($prop['CODE'], array('creation_datetime', 'edit_datetime')))
					$prop['VALUE'] = date(GetMessage("PR_RECRM_C_DATETIME"), $prop['VALUE']);

				$arItem['PROPERTIES_RECRM'][$prop['CODE']] = $prop;
			endif;
		}

		$arResult = $arItem;

		$arResult["IBLOCK"] 		= GetIBlock($arResult["IBLOCK_ID"], $arResult["IBLOCK_TYPE"]);
		$arResult["SECTION"] 		= array("PATH" => array());
		$arResult["SECTION_URL"] 	= "";

		$this->SetResultCacheKeys(array(
			"ID",
			"IBLOCK_ID",
			"NAV_CACHED_DATA",
			"NAME",
			"IBLOCK_SECTION_ID",
			"IBLOCK",
			"LIST_PAGE_URL", "~LIST_PAGE_URL",
			"SECTION_URL",
			"SECTION",
			"PROPERTIES",
			"IPROPERTY_VALUES",
		));

		$this->IncludeComponentTemplate();
	}
	else
	{
		$this->AbortResultCache();
		ShowError("������� �� ������!");
		@define(GetMessage("PR_RECRM_C_ERR_404"));
		if($arParams["SET_STATUS_404"]==="Y")
			CHTTP::SetStatus("404 Not Found");
	}
}

if(isset($arResult["ID"]))
{
	$arTitleOptions = null;
	if(CModule::IncludeModule("iblock"))
	{
		CIBlockElement::CounterInc($arResult["ID"]);

		if($USER->IsAuthorized())
		{
			if(
				$APPLICATION->GetShowIncludeAreas()
				|| $arParams["SET_TITLE"]
				|| isset($arResult[$arParams["BROWSER_TITLE"]])
			)
			{
				$arReturnUrl = array(
					"add_element" => CIBlock::GetArrayByID($arResult["IBLOCK_ID"], "DETAIL_PAGE_URL"),
					"delete_element" => (
						empty($arResult["SECTION_URL"])?
						$arResult["LIST_PAGE_URL"]:
						$arResult["SECTION_URL"]
					),
				);

				$arButtons = CIBlock::GetPanelButtons(
					$arResult["IBLOCK_ID"],
					$arResult["ID"],
					$arResult["IBLOCK_SECTION_ID"],
					Array(
						"RETURN_URL" => $arReturnUrl,
						"SECTION_BUTTONS" => false,
					)
				);

				if($APPLICATION->GetShowIncludeAreas())
					$this->AddIncludeAreaIcons(CIBlock::GetComponentMenu($APPLICATION->GetPublicShowMode(), $arButtons));

				if($arParams["SET_TITLE"] || isset($arResult[$arParams["BROWSER_TITLE"]]))
				{
					$arTitleOptions = array(
						'ADMIN_EDIT_LINK' => $arButtons["submenu"]["edit_element"]["ACTION"],
						'PUBLIC_EDIT_LINK' => $arButtons["edit"]["edit_element"]["ACTION"],
						'COMPONENT_NAME' => $this->GetName(),
					);
				}
			}
		}
	}

	$this->SetTemplateCachedData($arResult["NAV_CACHED_DATA"]);

	if($arParams["SET_TITLE"])
	{
		if ($arResult["IPROPERTY_VALUES"]["ELEMENT_PAGE_TITLE"] != "")
			$APPLICATION->SetTitle($arResult["IPROPERTY_VALUES"]["ELEMENT_PAGE_TITLE"], $arTitleOptions);
		else
			$APPLICATION->SetTitle($arResult["NAME"], $arTitleOptions);
	}

	if ($arParams["SET_BROWSER_TITLE"] === 'Y')
	{
		$browserTitle = \Bitrix\Main\Type\Collection::firstNotEmpty(
			$arResult["PROPERTIES"], array($arParams["BROWSER_TITLE"], "VALUE")
			,$arResult, $arParams["BROWSER_TITLE"]
			,$arResult["IPROPERTY_VALUES"], "ELEMENT_META_TITLE"
		);
		if (is_array($browserTitle))
			$APPLICATION->SetPageProperty("title", implode(" ", $browserTitle), $arTitleOptions);
		elseif ($browserTitle != "")
			$APPLICATION->SetPageProperty("title", $browserTitle, $arTitleOptions);
	}

	if ($arParams["SET_META_KEYWORDS"] === 'Y')
	{
		$metaKeywords = \Bitrix\Main\Type\Collection::firstNotEmpty(
			$arResult["PROPERTIES"], array($arParams["META_KEYWORDS"], "VALUE")
			,$arResult["IPROPERTY_VALUES"], "ELEMENT_META_KEYWORDS"
		);
		if (is_array($metaKeywords))
			$APPLICATION->SetPageProperty("keywords", implode(" ", $metaKeywords), $arTitleOptions);
		elseif ($metaKeywords != "")
			$APPLICATION->SetPageProperty("keywords", $metaKeywords, $arTitleOptions);
	}

	if ($arParams["SET_META_DESCRIPTION"] === 'Y')
	{
		$metaDescription = \Bitrix\Main\Type\Collection::firstNotEmpty(
			$arResult["PROPERTIES"], array($arParams["META_DESCRIPTION"], "VALUE")
			,$arResult["IPROPERTY_VALUES"], "ELEMENT_META_DESCRIPTION"
		);
		if (is_array($metaDescription))
			$APPLICATION->SetPageProperty("description", implode(" ", $metaDescription), $arTitleOptions);
		elseif ($metaDescription != "")
			$APPLICATION->SetPageProperty("description", $metaDescription, $arTitleOptions);
	}

	if($arParams["INCLUDE_IBLOCK_INTO_CHAIN"] && isset($arResult["IBLOCK"]["NAME"]))
	{
		$APPLICATION->AddChainItem($arResult["IBLOCK"]["NAME"], $arResult["~LIST_PAGE_URL"]);
	}

	if($arParams["ADD_SECTIONS_CHAIN"] && is_array($arResult["SECTION"]))
	{
		foreach($arResult["SECTION"]["PATH"] as $arPath)
		{
			if ($arPath["IPROPERTY_VALUES"]["SECTION_PAGE_TITLE"] != "")
				$APPLICATION->AddChainItem($arPath["IPROPERTY_VALUES"]["SECTION_PAGE_TITLE"], $arPath["~SECTION_PAGE_URL"]);
			else
				$APPLICATION->AddChainItem($arPath["NAME"], $arPath["~SECTION_PAGE_URL"]);
		}
	}
	if ($arParams["ADD_ELEMENT_CHAIN"])
	{
		if ($arResult["IPROPERTY_VALUES"]["ELEMENT_PAGE_TITLE"] != "")
			$APPLICATION->AddChainItem($arResult["IPROPERTY_VALUES"]["ELEMENT_PAGE_TITLE"]);
		else
			$APPLICATION->AddChainItem($arResult["NAME"]);
	}

	return $arResult["ID"];
}
else
{
	return 0;
}