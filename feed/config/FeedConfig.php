<?php


class FeedConfig {

    public $feedData;
    public $productsCategory;
	public $productsIds;
	public $productsId;
    public $manufactures ;
    public $product_options;
    public $product_option_values;
	public $productAttributes;
	public $defaultsShipping;
	public $defaultPAvailability;
	public $defaultSCost;
	public $defaultTRate;
	public $storePickup;
	public $taxZone;
	public $perItemCost;
	public $deliveryTime;
	public $shipping;
	public $attToFeed;
	public $productsWithAttributes;
	public $extraAttributes = array();
	public $shippingAttributes = array();
	public static $gReturn = array (
        'ModelOwn'              => 'ModelOwn',
        'Name'                  => 'Name',
        'Subtitle'              => 'Subtitle',
        'Description'           => 'Description',
        'AdditionalInfo'        => 'AdditionalInfo',
        'Image'                 => 'Image',
        'Manufacturer'          => 'Manufacturer',
        'Model'                 => 'Model',
        'Category'              => 'Category',
        'CategoriesGoogle'      => 'CategoriesGoogle',
        'CategoriesYatego'      => 'CategoriesYatego',
        'ProductsEAN'           => 'ProductsEAN',
        'ProductsISBN'          => 'ProductsISBN',
        'Productsprice_brut'    => 'Productsprice_brut',
        'Productspecial'        => 'Productspecial',
        'Productsprice_uvp'     => 'Productsprice_uvp',
        'BasePrice'             => 'BasePrice',
        'BaseUnit'              => 'BaseUnit',
        'Productstax'           => 'Productstax',
        'ProductsVariant'       => 'ProductsVariant',
        'Currency'              => 'Currency',
        'Quantity'              => 'Quantity',
        'Weight'                => 'Weight',
        'AvailabilityTxt'       => 'AvailabilityTxt',
        'Condition'             => 'Condition',
        'Coupon'                => 'Coupon',
        'Gender'                => 'Gender',
        'Size'                  => 'Size',
        'Color'                 => 'Color',
        'Material'              => 'Material',
        'Packet_size'           => 'Packet_size',
        'DeliveryTime'          => 'DeliveryTime',
        'Shipping'              => 'Shipping',
        'ShippingAddition'      => 'ShippingAddition',
        'shipping_paypal_ost'   => 'shipping_paypal_ost',
        'shipping_cod'          => 'shipping_cod',
        'shipping_credit'       => 'shipping_credit',
        'shipping_paypal'       => 'shipping_paypal',
        'shipping_transfer'     => 'shipping_transfer',
        'shipping_debit'        => 'shipping_debit',
        'shipping_account'      => 'shipping_account',
        'shipping_moneybookers' => 'shipping_moneybookers',
        'shipping_giropay'      => 'shipping_giropay',
        'shipping_click_buy'    => 'shipping_click_buy',
        'shipping_comment'      => 'shipping_comment'
	);
    public $locale ;
	public $base_price;
	public $price;
	public $special;
	public $specialPrice;
	public $tax_rate;

	protected $categoryParent;
	protected $categoryPath;

	//the rule is: key->admin panel fields name with prefix FEEDIFY_FIELD_
	//value->name of field which is extracted from db
	//if is necessary to add a new field simply add here a new item and
	//in function getFeedColumnValue set the value to export like this: $oArticle["coupon"]
	protected $parameters = array(
		"EAN" => "ean",
		"ISBN" => "isbn",
		"BASE_UNIT" => "base_unit",
		"UVP" => "uvp",
		"YATEGOO" => "yategoo",
		"PACKET_SIZE" => "packet_size",
		"SUBTITLE" => "subtitle",
		"COLOR" => "color",
		"SIZE" => "size",
		"GENDER" => "gender",
		"MATERIAL" => "material",
		"COUPON" => "coupon",
		"AUTO_MANUFACTURER" => "auto_manufacturer"
	);

	public function __construct()
	{
		$this->_initParameters();
	}

    /**
     * update database
     */
    public function remove()
    {
        $db = $GLOBALS['db'];

        $db->Execute( "
            DELETE FROM " . TABLE_CONFIGURATION . "
            WHERE configuration_key LIKE '%FEED_%'"
        );
    }

    /**
     * save data in database
     */
	public function install()
	{
		$db = $GLOBALS['db'];

		foreach($_POST as $feedifyField => $value) {
			if(strpos($feedifyField,'FEED_') !== false) {
				$db->Execute( "
                    INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value)
                    VALUES ('". $feedifyField ."','" . $value ."' )"
				);
			}
		}
	}

	/**
	 * @param $string
	 * @return string
	 */
	//get the user data from database, example : getConfig('FEEDIFY_PASSWORD')
	public function getConfig($string)
	{
		$db = $GLOBALS['db'];
		$config = $db->Execute( "SELECT configuration_value FROM " . TABLE_CONFIGURATION . " WHERE configuration_key LIKE '$string' " );

		return $config->fields['configuration_value'];
	}

	/**
	 * @return array
	 */
	//select from db all languages and stock it into array
	public function getLanguagesArray(){
		$db = $GLOBALS['db'];
		$query = $db->Execute( "SELECT languages_id as id, code, name FROM " . TABLE_LANGUAGES );

		$rez = $this->dataFetch($query);

		return $rez;
	}

	/**
	 * @return array
	 */
	//select from db currencyes and stock it into array
	public function getCurrencyArray()
	{
		$db = $GLOBALS['db'];
		$query = $db->Execute( "SELECT currencies_id as id, code, title FROM " . TABLE_CURRENCIES );

		$rez = $this->dataFetch($query);

		return $rez;
	}

	/**
	 * @return array
	 */
	public function getTaxZones(){
		$db = $GLOBALS['db'];
		$result = $db->Execute( "SELECT  geo_zone_id, geo_zone_name FROM " .TABLE_GEO_ZONES );

		$rez = array();
		while ( !$result->EOF ) {
			$rez[$result->fields['geo_zone_id']] = $result->fields['geo_zone_name'];
			$result->MoveNext();
		}

		return $rez;
	}

	public function getShopLanguageConfig()
	{
		$oConfig = new stdClass();
		$aLanguages = $this->getLanguagesArray();
		$oConfig->key = "language";
		$oConfig->title = "language";
		foreach ($aLanguages as $language) {
			$oValue = new stdClass();
			$oValue->key = $language['code'];
			$oValue->title = $language['name'];
			$oConfig->values[] = $oValue;
		}

		return $oConfig;
	}


    /**
     * @return stdClass
     */
    public function getShopCondition()
    {
        $values = array(
            0 => 'export_all_products',
            1 => 'export_active_products',
            2 => 'export_products_in_stock',
            3 => 'export_active_products_in_stock',
        );

        $stdConfig = new stdClass();
        $stdConfig->key = 'status';
        $stdConfig->title = 'status';
        foreach ($values as $key => $title) {
            $stdValue = new stdClass();
            $stdValue->key = $key;
            $stdValue->title = $title;
            $stdConfig->values[] = $stdValue;
        }

        return $stdConfig;
    }

	public function getShopAvailabilityConfig()
	{
		$oConfig = new stdClass();
		$aAvailabilities[] = array('id' => '1', 'title' => 'No export inactive and with quantity = 0 products');
		$aAvailabilities[] = array('id' => '2', 'title' => 'Export inactive No export with quantity = 0 products');
		$aAvailabilities[] = array('id' => '3', 'title' => 'No export inactive Export with quantity = 0 products');
		$aAvailabilities[] = array('id' => '4', 'title' => 'Export inactive and with quantity = 0 products');
		$oConfig->key = "status";
		$oConfig->title = "Status";
		foreach($aAvailabilities as $oAvailability) {
			$oValue = new stdClass();
			$oValue->key = $oAvailability['id'];
			$oValue->title = $oAvailability['title'];
			$oConfig->values[] = $oValue;
		}

		return $oConfig;
	}

	public function getShopCurrencyConfig()
	{
		$oConfig = new stdClass();
		$aCurrencies =  $this->getCurrencyArray();
		$oConfig->key = "currency";
		$oConfig->title = "currency";
		foreach($aCurrencies as $oCurrency) {
			$oValue = new stdClass();
			$oValue->key = $oCurrency['code'];
			$oValue->title = $oCurrency['title'];
			$oConfig->values[] = $oValue;
		}

		return $oConfig;
	}

	public function getQueryFields()
	{
		return array(
			'id' => 'id',
			'quantity' => 'quantity',
			'model' => 'model',
			'image' => 'image',
			'price' => 'price',
			'weight' => 'weight',
			'status' => 'status',
			'always_free_shipping' => 'always_free_shipping',
			'master_categories_id' => 'master_categories_id',
			'tax_class_id' => 'tax_class_id',
			'manufacturers_name' => 'manufacturers_name',
			'parent_id' => 'parent_id',
			'language_id' => 'language_id',
			'products_name' => 'products_name',
			'products_description' => 'products_description',
			'currencies_code' => 'currencies_code',
			'currencies_decimal_places' => 'currencies_decimal_places',
			'currencies_value' => 'currencies_value',
			'special_price' => 'special_price',
		);
	}

    public function getProducts($limit, $offset, $queryParameters){
        $db = $GLOBALS['db'];
        $select = '
                SELECT
                    p.products_id as products_id,
                    p.products_quantity as products_quantity,
                    p.products_model as products_model,
                    p.products_image as products_image,
                    p.products_price as products_price,
                    p.products_weight as products_weight,
                    p.manufacturers_id as manufacturers_id,
                    p.products_tax_class_id as products_tax_class_id,
                    pd.products_name as products_name,
                    pd.language_id as language_id,
                    pd.products_description as products_description,
                    pd.products_url as products_url,
                    p.products_status as products_status

        ';
        /*$select  = '
                SELECT
                    *
        ';*/
        $from = ' FROM
                    '.TABLE_PRODUCTS.' p
                  inner join '.TABLE_PRODUCTS_DESCRIPTION.' pd on p.products_id=pd.products_id
        ';

        $where = '';

        /*
            0 => 'out of stock',
            1 => 'in stock'
         */
        if($queryParameters->status ) {
            switch ($queryParameters->status) {

                case 1:
                    $where = '
                    where p.products_status = 1
                ';
                    break;
                case 2:
                    $where = '
                    where p.products_quantity  > 0
                ';
                    break;
                case 3:
                    $where = '
                    where p.products_quantity > 0
                    and p.products_status = 1
                ';
            }
        }
        if($queryParameters->language){
            $where .= ' and pd.language_id = '.$this->locale[$queryParameters->language];

        }
        $dimensions = ' limit '.$limit.'  offset '.$offset;
        $query  = $select.$from.$where.$dimensions;
        $response = $this->dataFetch($db->Execute($query), true);
        $temp = array();

        foreach ($response as $item) {
            $temp[] = $item['products_id'];
        }
        $this->productsId = implode(',',$temp);
        $this->getSpecialPrices();
        //var_dump($response);die;
        return  $response;
    }


    public function getProductsAttr(){
        /*$var = $this->getOrdersProducts(1,9);
        $products = '';
        foreach ($var as $product) {
            //$tax = zen_get_tax_rate($product['tax_class_id'], $config->taxZone['zone_country_id'], $config->taxZone['zone_id']);
            $price = $product['product']['price'];
            $quantity = $product['product']['qty'] ;
            $products .= $product['attributes']['ModelOwn']."=".$price."=".$quantity.";";
        }
        var_dump($products, $var);die;*/
        $db = $GLOBALS['db'];
        $query  = '
                    select
                        pa.products_attributes_id as products_attributes_id,
                        pa.products_id as products_id,
                        pa.options_id as options_id,
                        pa.options_values_id as options_values_id,
                        pa.options_values_price as options_values_price,
                        pa.price_prefix as price_prefix,
                        pa.products_attributes_weight as attributes_weight,
                        pa.products_attributes_weight_prefix as attributes_weight_prefix
                    from '.TABLE_PRODUCTS_ATTRIBUTES.' pa
                    where pa.products_id in ('.$this->productsId.')

        ';

        /*$query  = '
                    select
                        *
                    from '.TABLE_PRODUCTS_ATTRIBUTES.' pa
                    where pa.products_id in ('.$this->productsId.')

        ';*/

        $response = $this->dataFetch($db->Execute($query), true);

        $lastProductId = null;
        $idList = array();
        $temp = array();
        //make from a lot of arrays one single array which fields will be arrays with all possible data
        foreach ($response[0] as $key=>$value) {
            $temp[$key] = array();
        }

        foreach ($response as $item) {
            if($lastProductId != $item['products_id'] ){
                $idList[] = $item['products_id'];
            }
            $lastProductId = $item['products_id'];
        }
        foreach ($idList as $item) {
            foreach ($response as $attribute) {
                if($item == $attribute['products_id']){
                    foreach ($attribute as $key=>$value) {
                        $temp[$item][$key][] = $value;
                    }
                }
            }
            $temp[$item]['options_list'] = array();
        }
        foreach ($temp as $key=>$item) {
            if(empty($item)){
                unset($temp[$key]);
            }
        }
        $option_var = null;
        $option_array = array();
        foreach ($temp as $key=>$item) {
            foreach ($item['options_id'] as $element) {
                if($element != $option_var){
                    $option_array[$key][] =  (int) $element ;
                }
                $option_var = $element;
            }
            $buff = array();
            foreach ($option_array[$key] as $value) {
                foreach ($temp[$key]['options_id'] as $key2 => $element) {
                    if($value == $element) {
                        $buff[$value][$key2] = $temp[$key]['options_values_id'][$key2];
                    }
                }
            }
            $temp[$key]['options_list'] = $buff ;
        }
        $this->productAttributes = $temp ;

        return $temp;
    }


	//function for checking if column products_id exist in tables $table
	protected function _checkTables($tables)
	{
		$db = $GLOBALS['db'];
		$output = array();

		foreach($tables as $key => $table) {
			if($table != 'N' && $table !== null) {
				$tables[$key] = "'".strtok($table, ';')."'";
			} else {
				unset ($tables[$key]);
			}
		}

		if($tables) {
			$query = ("
				SELECT DISTINCT c.column_name, c.table_name FROM information_schema.columns AS c
				WHERE table_name IN ( ".implode(',', $tables)." ) AND TABLE_SCHEMA = '$db->database'
			");

			$result = $db->Execute($query);

			while(!$result->EOF) {
				$output[$result->fields['table_name']][] = $result->fields['column_name'];
				$result->MoveNext();
			}

			foreach($output as $key_1 => $inspector) {
				if(!in_array('products_id', $inspector)) {
					foreach($this->parameters as $key_2 => $parameter) {
						if(strtok($parameter, ';') == $key_1) {
							unset($this->parameters[$key_2]);
						}
					}
				}
			}
		}
	}

    public function generate($index, $attributes, $options, $product_id)
    {
        $attributes[$this->productAttributes[$product_id][$index]['products_attributes_id']] = $this->productAttributes[$product_id][$index]['products_attributes_id'];
        $options[$this->productAttributes[$product_id][$index]['options_id']] = $this->productAttributes[$product_id][$index]['options_id'];
        $withRequired = array_diff($this->productAttributes[$product_id]['required'], $options);
        if (empty($withRequired)) {
            $combinations[] = $attributes;
        } else $combinations = array();

        for ($i = $index + 1; $i < count($this->productAttributes[$product_id])-1; $i++) {
            if ($this->productAttributes[$product_id][$index]['options_id'] != $this->productAttributes[$product_id][$i]['options_id']) {
                $combinations = array_merge($combinations, $this->generate($i, $attributes, $options, $product_id));
            }
        }

        return $combinations;
    }


    public function getProductsAttributes($ids = array(), $products_ids = array())
    {
        $db = $GLOBALS['db'];
        $query = "
            SELECT	pa.products_id AS id,
	        pov.products_options_values_name,

            pa.options_id,
            pa.options_values_price,
            pa.products_attributes_id,
            pa.price_prefix,
            pa.options_values_id,
            pa.attributes_required,
            po.products_options_type,
            pa.products_attributes_weight_prefix AS weight_prefix,
            pa.attributes_image,
            pa.products_attributes_weight,
            po.products_options_name

            FROM	".TABLE_PRODUCTS_ATTRIBUTES." pa

            LEFT JOIN ".TABLE_PRODUCTS_OPTIONS." po
            ON (po.products_options_id = pa.options_id)

            LEFT JOIN ".TABLE_PRODUCTS_OPTIONS_VALUES." pov
            ON (pa.options_values_id = pov.products_options_values_id)
        ";

        if($ids && $products_ids) {
            $query .= ' WHERE pa.products_attributes_id IN ('.implode(',', $ids).')
                        AND pa.products_id IN ('.implode(',', $products_ids).')';
        }

		if($products_ids && !$ids) {
			$query .= ' WHERE pa.products_id IN ('.implode(',', $products_ids).')';
		}

        $resource = $db->Execute($query);

        $pAttributes = $this->dataFetch($resource);

        if (!$pAttributes) {
            $this->productAttributes = array();
        } else {
            foreach ($pAttributes as $attribute) {
                $this->productAttributes[$attribute['id']][$attribute['products_attributes_id']] = $attribute;
            }
        }

    }

	//get and analyze the shipping parameters and set priority of fields
	public function getFeedifyShippingParameters()
	{
		$db = $GLOBALS['db'];		//database

		$query = "
				SELECT configuration_key, configuration_value
				FROM ".TABLE_CONFIGURATION."
				WHERE configuration_key LIKE '%FEED_SHIPPING%'
			";

		$result = $this->dataFetch($db->Execute($query));

		foreach($result as $key => $item) {
			if(strstr($item['configuration_key'], '1') && $item['configuration_value'] != 'N' && $item['configuration_value'] !== null) {
				$this->defaultsShipping[$item['configuration_key']] = $item['configuration_value'];
			}

			if(strstr($item['configuration_key'], '2') && $item['configuration_value'] != 'N' && $item['configuration_value'] !== null) {
				$this->parameters[$item['configuration_key']] = $item['configuration_value'];
			}

			if(strstr($item['configuration_key'], '3') && $item['configuration_value'] != 'N' && $item['configuration_value'] !== null) {
				$temp = strtolower(substr($item['configuration_key'], 8, -2));
				$this->shippingAttributes[$temp] = $item['configuration_value'];
			}
		}
	}

    public function dataFetch($resource, $setIds = false)
    {
        $output = array(); //if is set parameter $setIds function store ids of fetched data to $this->productsIds
        if($resource->fields) {
            while (!$resource->EOF) {
				if($setIds === true) {
					$this->productsIds[] = $resource->fields['id'];
				}
                $output[] = $resource->fields;
                $resource->MoveNext();
            }
        } else {

            return $output;
        }

        return $output;
    }

    public function getAttributesGroups()
    {
        $db = $GLOBALS['db'];

        $query = "
            SELECT products_options_name, products_options_id
            FROM ".TABLE_PRODUCTS_OPTIONS."
        ";

        $result = $db->Execute($query);

        return $this->dataFetch($result);
    }

	//acceptable keywords format : "'key_1', 'key_2', 'key_3'" !!pay attention at brackets!!
	public function getDatabaseColumns($keywords) {
		$db = $GLOBALS['db'];

		$query = "
			SELECT DISTINCT c.column_name, c.table_name
			FROM information_schema.columns AS c
			WHERE TABLE_SCHEMA = '".$db->database."'
			AND c.table_name IN ($keywords)"
		;
		$result = $this->dataFetch($db->Execute($query));

		return $result;
	}

	protected function _getOrdersAttributes($id)
	{
		$db = $GLOBALS['db'];
        $select = '
                select
                    op.products_id as products_id,
                    op.products_model as products_model,
                    op.products_name as products_name,
                    op.orders_products_id as orders_products_id,
                    op.orders_id as orders_id,
                    op.products_price as products_price

        ';

        $from = '
                from '.TABLE_ORDERS_PRODUCTS.' op
        ';

        $where = '
                where op.orders_id ='.$id
        ;

        $query = $select.$from.$where;


		$products = $db->Execute($query);
        $products = $this->dataFetch($products);
        $temp = array();
        $buff = array();
        foreach ($products as $item) {
            $temp[] = $item['orders_products_id'];
            $item['ModelOwn'] = '';
            $buff[$item['orders_products_id']] = $item;
        }

        $products = $buff;
        $query = '
            select
                    opa.orders_products_id as orders_products_id,
                    po.products_options_id as products_options_id,
                    pov.products_options_values_id as products_options_values_id

            from '.TABLE_ORDERS_PRODUCTS_ATTRIBUTES.' opa

            inner join '.TABLE_PRODUCTS_OPTIONS.' po on po.products_options_name = opa.products_options
            inner join '.TABLE_PRODUCTS_OPTIONS_VALUES.' pov on pov.products_options_values_name = opa.products_options_values
            where opa.orders_products_id in ('.implode(',',$temp).')
        ';

        $attributes = $db->Execute($query);
        $attributes = $this->dataFetch($attributes);
        unset($buff);
        $buff = array();
        foreach ($attributes as $element) {
            $a = $element['orders_products_id'];
            unset($element['orders_products_id']);
            $buff[$a] = $element ;

        }

        $attributes = $buff;
        unset($buff);
        $buff = array();

        foreach ($products as $key=>$value) {
            if(array_key_exists($key,$attributes)){
                $value['ModelOwn'] = $value['products_id'].'_'.implode('-',$attributes[$key]) ;
                $buff[$key] = $value ;
            } else {
                $value['ModelOwn'] = $value['products_id'] ;
                $buff[$key] = $value ;
            }
        }
        $var = array() ;
        foreach ($buff as $element) {
            $var[$element['products_id']] = $element ;
        }

        return $var ;
	}







    public function getOrdersProducts($currency, $id, $print = true, $tracking = false)
    {
        $products = $this->_getOrdersProducts($id, $currency);
        $attributes = $this->_getOrdersAttributes($id);
        $temp = array();
        foreach ($products as $key=>$value) {
            $temp[$key]['product'] = $value ;
            $temp[$key]['attributes'] = $attributes[$key] ;
        }

        return $temp;
    }

    public function getAttributes()
    {
        $attributes = array();

        return array_merge($attributes, $this->_getAllAttributesCombo());
    }

    protected function _getAllAttributesCombo()
    {
        $results = array();
        foreach($this->productAttributes as $product_id => $attributes) {
            $result = array();
            ksort($this->productAttributes[$product_id]);
            foreach ($attributes as $attribute) {
                if (in_array($attribute['products_options_type'], array("0", "2"))) {
                    $this->productAttributes[$product_id]['required'][$attribute['options_id']] = $attribute['options_id'];
                }
            }

            $this->productAttributes[$product_id] = array_merge($this->productAttributes[$product_id], array());
            for ($i = 0; $i < count($this->productAttributes[$product_id]); $i++) {
                $result = array_merge($result, $this->generate($i, array(), array(), $product_id));
            }

            $results[$product_id] = $result;
        }

        return $results;
    }

	protected function _getOrdersProducts($id, $currency)
	{
		$db = $GLOBALS['db'];
		$query = "
			SELECT	op.final_price AS price,
                    op.products_quantity AS qty,
                    op.products_id AS id,
                    p.products_tax_class_id AS tax_class_id,

                    c.code

            FROM	".TABLE_ORDERS_PRODUCTS." op

            LEFT JOIN ".TABLE_CURRENCIES." c
            ON (c.currencies_id = ".$currency.")

            LEFT JOIN ".TABLE_PRODUCTS." p
            ON (op.products_id = p.products_id)

            WHERE	op.orders_id = ".$id
		;
		$result = $db->Execute($query);
        $result = $this->dataFetch($result) ;
        $temp = array();
        foreach ($result as $item) {
            $temp[$item['id']] = $item ;
        }

        return $temp ;
	}

	/*
	 * function is used to initialize
	 * the shipping parameters and extra attributes
	 * shipping parameters - data from admin feed form
	 * extra attributes - fields with prefix FEEDIFY_EATTRIBUTES from admin feed form
	 */
	protected function _initParameters()
	{
		foreach($this->parameters as $key => $parameter) {
			$this->parameters[$parameter] = $this->getConfig("FEED_FIELD_".$key);
			unset($this->parameters[$key]);
		}

		$this->getFeedifyShippingParameters();
		//$this->_iniExtraAttributesParameters();
        $this->setProductsOptions();
        $this->setManufactures();
        $this->setCategories();
        $this->getFeedifyFormData();
        $this->setLocale();
	}

    public function setLocale(){
        $query = '
                select
                    languages_id,
                    code
                from '.TABLE_LANGUAGES.'
        ';
        $db = $GLOBALS['db'];
        $temp = $this->dataFetch($db->Execute($query));
        $array = array();
        foreach ($temp as $item) {
            $array[$item['code']] = $item['languages_id'];
        }
        $this->locale = $array;
    }

    public function getSpecialPrices(){
        $query = '
                select
                    s.products_id as products_id,
                    s.specials_new_products_price as specials_new_products_price ,
                    s.expires_date as expires_date,
                    s.status as status,
                    s.specials_date_available
                from '.TABLE_SPECIALS.' s
                where s.products_id in ('.$this->productsId.')
        ';
        $db = $GLOBALS['db'];
        $temp = $this->dataFetch($db->Execute($query));
        $array = array();
        foreach ($temp as $item) {
            $array[$item['products_id']] = $item;
        }
        $this->specialPrice = $array ;
    }


    public function getFeedifyFormData(){
        $query = '
                select
                    c.configuration_value as 	configuration_value,
                    c.configuration_key as configuration_key
                from '.TABLE_CONFIGURATION.' c
                where c.configuration_key like "%FEED%"
        ';
        $db = $GLOBALS['db'];
        $temp = $this->dataFetch($db->Execute($query));
        $array = array();
        foreach ($temp as $item) {
            $array[$item['configuration_key']] = $item['configuration_value'];
        }
        $this->feedData = $array ;
    }

    public function setCategories(){
        $query = '
                    select
                        c.categories_id as categories_id ,
                        c.parent_id as parent_id ,
                        cd.categories_name as categories_name

                    from '.TABLE_CATEGORIES.' c
                    inner join '.TABLE_CATEGORIES_DESCRIPTION.' cd  on cd.categories_id=c.categories_id
        ';
        $db = $GLOBALS['db'];
        $temp = $this->dataFetch($db->Execute($query));
        $result = array();
        foreach ($temp as $item) {
            $result[$item['categories_id']] = $item ;
        }
        $this->productsCategory = $result ;
    }

    public function setTaxRate(){
        $query = 'select
                    t.tax_class_id as tax_class_id,
                    t.tax_rate as tax_rate
                  from '.TABLE_TAX_RATES.' t
        ';
        $db = $GLOBALS['db'];
        $this->tax_rate = $this->dataFetch($db->Execute($query));
    }

    public function setManufactures(){
        $query = '
                select
                    m.manufacturers_id as manufacturers_id,
                    m.manufacturers_name as manufacturers_name
                from '.TABLE_MANUFACTURERS.' m
        ';
        $db = $GLOBALS['db'];
        $manufactures  = $this->dataFetch($db->Execute($query));
        $temp = array();
        foreach ($manufactures as $key => $value) {
            $temp[$value['manufacturers_id']] = $value['manufacturers_name'];
        }
        $this->manufactures=$temp;
    }

    public function setProductsOptions(){

        $db = $GLOBALS['db'];

        $select_options = '
                    select
                        po.products_options_id as products_options_id,
                        po.products_options_name as products_options_name,
                        po.products_options_length as products_options_length,
                        po.products_options_size as products_options_size

                    from '.TABLE_PRODUCTS_OPTIONS.' po
        ';

        $result1 = $this->dataFetch($db->Execute($select_options));
        $temp = array();
        foreach ($result1 as $key=>$value) {
            $temp[$value['products_options_id']] = $value ;
        }
        //var_dump($temp);die;
        $this->product_options  = $temp;

        $select_options_attributes = '
                    select
                        pov.products_options_values_id as options_values_id,
                        pov.products_options_values_name as options_values_name
                    from '.TABLE_PRODUCTS_OPTIONS_VALUES.' pov
        ';
        $result2  = $this->dataFetch($db->Execute($select_options_attributes));
        $temp1 = array();
        foreach ($result2 as $result) {
            $temp1[$result['options_values_id']] = $result['options_values_name'] ;
        }
        $this->product_option_values = $temp1 ;
    }

//---------------------- functionality part



	/*
	 * initialize parameters for
	 * better usage and time economy
	 */
	public function iniParameters()
	{
		/*$this->_getAttributesParameters();*/
		$this->defaultPAvailability = $this->getConfig('FEED_FIELD_AVAILABILITY');
		$this->defaultSCost = $this->getConfig('FEED_FIELD_SHIPPING_COST');
		$this->defaultTRate = $this->getConfig('FEED_FIELD_TAX_RATE');
		$this->storePickup  = $this->getConfig('MODULE_SHIPPING_STOREPICKUP_COST');
		$this->taxZone      = $this->_getTaxZone();
		$this->perItemCost  = $this->getConfig('MODULE_SHIPPING_ITEM_COST');
		/*$this->deliveryTime = $this->_getDeliveryTime();*/
		$this->shipping     = $this->_initShipping();
		foreach( $this->shipping->modules as $key=>$module){
			$GLOBALS[substr($module, 0, strrpos($module, '.'))]->enabled = true;
		}
	}



	protected function _getTaxZone()
	{
		$db = $GLOBALS['db'];
		$geoZoneId = $this->getConfig('FEED_TAX_ZONE');
		$taxZone = array();

		$zone = $db->Execute('
            SELECT zone_id, zone_country_id
            FROM '.TABLE_ZONES_TO_GEO_ZONES.'
            WHERE geo_zone_id = '.$geoZoneId
		);

		$zone = $this->dataFetch($zone);
		foreach ($zone as $item) {
			$taxZone['zone_id'] = $item['zone_id'];
			$taxZone['zone_country_id'] = $item['zone_country_id'];
		}

		return $taxZone;
	}


	protected function _initShipping()
	{
		if (!isset($this->shipping)) {
			require_once (DIR_WS_CLASSES.'shipping.php');
			$this->shipping = new shipping();
		}

		return $this->shipping;
	}

	protected function _addToCartContent($row){
		if( !$_SESSION['cart']) {
			$_SESSION['cart'] = new shoppingCart();
		}

		$_SESSION['cart']->contents = array();
		$_SESSION['cart']->contents[] = array($row['id']);
		$_SESSION['cart']->contents[$row['id']] = array('qty' => (int)1);

	}



    function allCombinations($arrays)
    {
        $result = array();
        $arrayKeys = array_keys($arrays);
        $arrays = array_values($arrays);
        $sizeIn = sizeof($arrays);
        $size = $sizeIn > 0 ? 1 : 0;
        foreach ($arrays as $array)
            $size = $size * sizeof($array);
        for ($i = 0; $i < $size; $i ++)
        {
            $result[$i] = array();
            for ($j = 0; $j < $sizeIn; $j ++)
                array_push($result[$i], current($arrays[$j]));
            for ($j = ($sizeIn -1); $j >= 0; $j --)
            {
                if (next($arrays[$j]))
                    break;
                elseif (isset ($arrays[$j]))
                    reset($arrays[$j]);
            }
        }
        $temp = array();
        foreach ($result as $key1=>$item) {
            foreach ($item as $key2=>$element) {
                $temp[$key1][$arrayKeys[$key2]] = $element ;
            }
        }

        return $temp;
    }

    public function uploadCSVfileWithCombinations($csv_file,$product,$attributes,$fieldMap, $shopConfig,$queryParameters){
        $allCombinations = $this->allCombinations($attributes[$product['products_id']]['options_list']);
        $row = array();
        if(array_key_exists($product['products_id'],$attributes) ){
            foreach ($allCombinations as $combinations) {
                foreach($fieldMap as $key => $field) {
                    $row[$key] = $this->getRowElements($field, $attributes, $product, $combinations, $shopConfig,$queryParameters);
                }
                fputcsv($csv_file, $row , ';', '"');
            }
        } else {
            foreach($fieldMap as $key => $field) {
                $row[$key] = $this->getRowElements($field, null, $product, null, $shopConfig, $queryParameters);
            }
            fputcsv($csv_file, $row, ';', '"');
        }
    }

    public function getRowElements($field, $attributes=null, $product, $combinations = null , $shopConfig, $queryParameters ){
        switch($field){
            case 'ModelOwn'              : {
                return $this->getModelOwn($product,$combinations);
            }
            case 'Name'                  : {
                return $product['products_name'];
            }
            case 'Subtitle'              : {
                return $this->getSubtitle($product,$combinations,$attributes);
            }
            case 'Description'           : {
                return $product['products_description'];
            }
            case 'AdditionalInfo'        : {
                return 'http://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['SCRIPT_NAME']).
                '/index.php?main_page=product_info&products_id='.$product['products_id'];
            }
            case 'Image'                 : {
                return 'http://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['SCRIPT_NAME']).
                "/images/".$product['products_image'];
            }
            case 'Manufacturer'          : {
                return $this->manufactures[$product['manufacturers_id']];
            }
            case 'Model'                 : {
                return $product['products_model'];
            }
            case 'Category'              : {
                return $this->getCategory($product);
            }
            case 'CategoriesGoogle'      : {
                return $this->getCategoriesGoogle($product,$combinations,$attributes);
            }
            case 'CategoriesYatego'      : {
                return $this->getCategoriesYatego($product,$combinations,$attributes);
            }
            case 'ProductsEAN'           : {
                return $this->getProductsEAN($product,$combinations,$attributes);
            }
            case 'ProductsISBN'          : {
                return $this->getProductsISBN($product,$combinations,$attributes);
            }
            case 'Productsprice_brut'    : {
                return $this->getProductsPriceBrut($product,$combinations,$attributes);
            }
            case 'Productspecial'        : {
                return $this->getProductSpecial($product);
            }
            case 'Productsprice_uvp'     : {
                return $this->getProductsPriceUVP($product,$combinations,$attributes);
            }
            case 'BasePrice'             : {
                return $this->getBasePrice($product,$combinations,$attributes);
            }
            case 'BaseUnit'              : {
                return $this->getBaseUnit($product,$combinations,$attributes);
            }
            case 'Productstax'           : {
                return $this->getProductTax($product);
            }
            case 'ProductsVariant'       : {
                return $this->getProductVariants($attributes,$product,$combinations);
            }
            case 'Currency'              : {
                return $queryParameters->currency ?: 'USD';
            }
            case 'Quantity'              : {
                return $product['products_quantity'];
            }
            case 'Weight'                : {
                return $this->getWeight($product,$combinations,$attributes);
            }
            case 'AvailabilityTxt'       : {
                return $this->getAvailability($product);
            }
            case 'Condition'             : {
                return $this->getCondition();
            }
            case 'Coupon'                : {
                return $this->getCoupon($product,$combinations,$attributes);
            }
            case 'Gender'                : {
                return $this->getGender($product,$combinations,$attributes);
            }
            case 'Size'                  : {
               return $this->getSize($product,$combinations,$attributes);
            }
            case 'Color'                 : {
                return $this->getColor($product,$combinations,$attributes);
            }
            case 'Material'              : {
                return $this->getMaterial($product,$combinations,$attributes);
            }
            case 'Packet_size'           : {
               return $this->getPacketSize($product,$combinations,$attributes);
            }
            case 'DeliveryTime'          : {
               return $this->getDeliveryTime($product,$combinations,$attributes);
            }
            case 'Shipping'              : {
                return $this->getShipping($product,$combinations,$attributes);
            }
            case 'ShippingAddition'      : {
                return $this->getShippingAddition($product);
            }
            case 'shipping_paypal_ost'   : {
                return $this->getShippingPaypalCost($product);
            }
            case 'shipping_cod'          : {
                return $this->getShippingCode($product);
            }
            case 'shipping_credit'       : {
                return $this->getShippingCredit($product);
            }
            case 'shipping_paypal'       : {
                return $this->getShippingPaypal($product);
            }
            case 'shipping_transfer'     : {
                return $this->getShippingTransfer($product);
            }
            case 'shipping_debit'        : {
                return $this->getShippingDebit($product);
            }
            case 'shipping_account'      : {
                return $this->getShippingAccount($product);
            }
            case 'shipping_moneybookers' : {
                return $this->getShippingMoneybookers($product);
            }
            case 'shipping_giropay'      : {
                return $this->getShippingGiropay($product);
            }
            case 'shipping_click_buy'    : {
                return $this->getShippingClickBy($product);
            }
            case 'shipping_comment'      : {
                return $this->getShippingComment();
            }
        }
    }


    public function getSubtitle($product,$combinations,$attributes){
        if($this->feedData['FEED_FIELD_SUBTITLE_1'] != 'N' and is_string($this->feedData['FEED_FIELD_SUBTITLE_1'])){
            $temp = explode(';',$this->feedData['FEED_FIELD_SUBTITLE_1']);
            $temp = $temp[1] ;
            if($product[$temp]){
                return  $product[$temp] ;
            }elseif ($this->feedData['FEED_FIELD_SUBTITLE_2'] != 'N' ){
                if( array_key_exists($this->product_options[$this->feedData['FEED_ATTRIBUTES_COLOR_2']]['products_options_id'] , $attributes[$product['products_id']]['options_list'] )){
                    return $this->product_option_values[$combinations[$this->product_options[$this->feedData['FEED_FIELD_SUBTITLE_2']]['products_options_id']]] ;
                }  elseif ( $this->feedData['FEED_FIELD_SUBTITLE_3'] != '' ){
                    return $this->feedData['FEED_FIELD_SUBTITLE_3'] ;
                }
            }
        }

        return '';
    }

    public function getCategoriesGoogle($product,$combinations,$attributes) {

        if($this->feedData['FEED_FIELD_GOOGLE_1'] != 'N' and is_string($this->feedData['FEED_FIELD_GOOGLE_1'])){
            $temp = explode(';',$this->feedData['FEED_FIELD_GOOGLE_1']);
            $temp = $temp[1] ;
            if($product[$temp]){
                return  $product[$temp] ;
            }
        }
        if ($this->feedData['FEED_FIELD_GOOGLE_2'] != 'N' ){
            if( array_key_exists($this->product_options[$this->feedData['FEED_FIELD_GOOGLE_2']]['products_options_id'] , $attributes[$product['products_id']]['options_list'] )) {
                return $this->product_option_values[$combinations[$this->product_options[$this->feedData['FEED_FIELD_GOOGLE_2']]['products_options_id']]];
            }
        }
        if ( $this->feedData['FEED_FIELD_GOOGLE_3'] != '' ) {
            return $this->feedData['FEED_FIELD_GOOGLE_3'];
        }

        return '';
    }

    public function getCategoriesYatego($product,$combinations,$attributes){
        if($this->feedData['FEED_FIELD_YATEGOO_1'] != 'N' and is_string($this->feedData['FEED_FIELD_YATEGOO_1'])) {
            $temp = explode(';', $this->feedData['FEED_FIELD_YATEGOO_1']);
            $temp = $temp[1];
            if ($product[$temp]) {
                return $product[$temp];
            }
        }
        if ($this->feedData['FEED_FIELD_YATEGOO_2'] != 'N' ) {
            if (array_key_exists($this->product_options[$this->feedData['FEED_FIELD_YATEGOO_2']]['products_options_id'], $attributes[$product['products_id']]['options_list'])) {
                return $this->product_option_values[$combinations[$this->product_options[$this->feedData['FEED_FIELD_YATEGOO_2']]['products_options_id']]];
            }
        }if ( $this->feedData['FEED_FIELD_YATEGOO_3'] != '' ){
            return $this->feedData['FEED_FIELD_YATEGOO_3'] ;
        }

        return '';
    }

    public function getProductsEAN($product,$combinations,$attributes){
        if($this->feedData['FEED_FIELD_EAN_1'] != 'N' and is_string($this->feedData['FEED_FIELD_EAN_1'])) {
            $temp = explode(';', $this->feedData['FEED_FIELD_EAN_1']);
            $temp = $temp[1];
            if ($product[$temp]) {
                return $product[$temp];
            }
        }
        if ($this->feedData['FEED_FIELD_EAN_2'] != 'N' ) {
            if (array_key_exists($this->product_options[$this->feedData['FEED_FIELD_EAN_2']]['products_options_id'], $attributes[$product['products_id']]['options_list'])) {
                return $this->product_option_values[$combinations[$this->product_options[$this->feedData['FEED_FIELD_EAN_2']]['products_options_id']]];
            }
        }
        if ( $this->feedData['FEED_FIELD_EAN_3'] != '' ){
            return $this->feedData['FEED_FIELD_EAN_3'] ;
        }

        return '';
    }

    public function getProductsISBN($product,$combinations,$attributes){
        if($this->feedData['FEED_FIELD_ISBN_1'] != 'N' and is_string($this->feedData['FEED_FIELD_ISBN_1'])) {
            $temp = explode(';', $this->feedData['FEED_FIELD_ISBN_1']);
            $temp = $temp[1];
            if ($product[$temp]) {
                return $product[$temp];
            }
        }
        if ($this->feedData['FEED_FIELD_ISBN_2'] != 'N' ) {
            if (array_key_exists($this->product_options[$this->feedData['FEED_FIELD_ISBN_2']]['products_options_id'], $attributes[$product['products_id']]['options_list'])) {
                return $this->product_option_values[$combinations[$this->product_options[$this->feedData['FEED_FIELD_ISBN_2']]['products_options_id']]];
            }
        }if ( $this->feedData['FEED_FIELD_ISBN_3'] != '' ){
            return $this->feedData['FEED_FIELD_ISBN_3'] ;
        }

        return '';
    }

    public function getProductsPriceBrut($product,$combinations,$attributes){
        if($attributes[$product['products_id']]){
            foreach ($combinations as $combination) {
                $a = $attributes[$product['products_id']]['options_values_price'][$combination];
                $b = $attributes[$product['products_id']]['price_prefix'][$combination];
                $c = $product['products_price'];
                $expression = $b.$a.$c;
                eval( '$result += (' . $expression . ');' );

                return ((($result)*$this->getProductTax($product)) / 100) + (+$result);
            }
        }

        return ((($product['products_price'])*$this->getProductTax($product)) / 100) + ($product['products_price']);
    }

    public function getProductSpecial($product){
        if($this->specialPrice[$product['products_id']]){
            $today = date("Y-m-d");
            $expireDate = $this->specialPrice['expires_date'];
            if($today < $expireDate) {
                return $this->specialPrice['specials_new_products_price'];
            }
        }

        return '';
    }

    public function getProductsPriceUVP($product,$combinations,$attributes){
        if($this->feedData['FEED_FIELD_UVP_1'] != 'N' and is_string($this->feedData['FEED_FIELD_UVP_1'])) {
            $temp = explode(';', $this->feedData['FEED_FIELD_UVP_1']);
            $temp = $temp[1];
            if ($product[$temp]) {
                return $product[$temp];
            }
        }
        if ($this->feedData['FEED_FIELD_UVP_2'] != 'N' ) {
            if (array_key_exists($this->product_options[$this->feedData['FEED_FIELD_UVP_2']]['products_options_id'], $attributes[$product['products_id']]['options_list'])) {
                return $this->product_option_values[$combinations[$this->product_options[$this->feedData['FEED_FIELD_UVP_2']]['products_options_id']]];
            }
        }
        if ( $this->feedData['FEED_FIELD_UVP_3'] != '' ){
            return $this->feedData['FEED_FIELD_UVP_3'] ;
        }

        return '';
    }

    public function getBasePrice($product,$combinations,$attributes){
        if($this->feedData['FEED_FIELD_BASE_PRICE_1'] != 'N' and is_string($this->feedData['FEED_FIELD_BASE_PRICE_1'])) {
            $temp = explode(';', $this->feedData['FEED_FIELD_BASE_PRICE_1']);
            $temp = $temp[1];
            if ($product[$temp]) {
                return $product[$temp];
            }
        }
        if ($this->feedData['FEED_FIELD_BASE_PRICE_2'] != 'N' ) {
            if (array_key_exists($this->product_options[$this->feedData['FEED_FIELD_BASE_PRICE_2']]['products_options_id'], $attributes[$product['products_id']]['options_list'])) {
                return $this->product_option_values[$combinations[$this->product_options[$this->feedData['FEED_FIELD_BASE_PRICE_2']]['products_options_id']]];
            }
        }
        if ( $this->feedData['FEED_FIELD_BASE_PRICE_3'] != '' ){
            return $this->feedData['FEED_FIELD_BASE_PRICE_3'] ;
        }

        return '';
    }

    public function getBaseUnit($product,$combinations,$attributes){
        if($this->feedData['FEED_FIELD_BASE_UNIT_1'] != 'N' and is_string($this->feedData['FEED_FIELD_BASE_UNIT_1'])) {
            $temp = explode(';', $this->feedData['FEED_FIELD_BASE_UNIT_1']);
            $temp = $temp[1];
            if ($product[$temp]) {
                return $product[$temp];
            }
        }
        if ($this->feedData['FEED_FIELD_BASE_UNIT_2'] != 'N' ) {
            if (array_key_exists($this->product_options[$this->feedData['FEED_FIELD_BASE_UNIT_2']]['products_options_id'], $attributes[$product['products_id']]['options_list'])) {
                return $this->product_option_values[$combinations[$this->product_options[$this->feedData['FEED_FIELD_BASE_UNIT_2']]['products_options_id']]];
            }
        }
        if ( $this->feedData['FEED_FIELD_BASE_UNIT_3'] != '' ){
            return $this->feedData['FEED_FIELD_BASE_UNIT_3'] ;
        }

        return '';
    }

    public function getWeight($product,$combinations,$attributes){
        if($attributes[$product['products_id']]){
            foreach ($combinations as $combination) {
                $a = $attributes[$product['products_id']]['attributes_weight'][$combination];
                $b = $attributes[$product['products_id']]['attributes_weight_prefix'][$combination];
                $c = $product['products_weight'];
                $expression = $b.$a.$c;
                eval( '$result += (' . $expression . ');' );
                return $result  ;
            }
        }
        return $product['products_weight'];
    }

    public function getAvailability($product){
        if ($product['availability'] == 0) {
            return 2;
        } else {
            return 1;
        }
    }

    public function getCondition(){
        if($this->feedData['FEED_FIELD_CONDITION_1'] != 'N'){
            return $this->feedData['FEED_FIELD_CONDITION_1'];
        } elseif ($this->feedData['FEED_FIELD_CONDITION_2'] != '') {
            return $this->feedData['FEED_FIELD_CONDITION_2'];
        }
        return '';
    }

    public function getCoupon($product,$combinations,$attributes){
        if($this->feedData['FEED_FIELD_COUPON_1'] != 'N' and is_string($this->feedData['FEED_FIELD_COUPON_1'])) {
            $temp = explode(';', $this->feedData['FEED_FIELD_COUPON_1']);
            $temp = $temp[1];
            if ($product[$temp]) {
                return $product[$temp];
            }
        }
        if ($this->feedData['FEED_FIELD_COUPON_2'] != 'N' ) {
            if (array_key_exists($this->product_options[$this->feedData['FEED_FIELD_COUPON_2']]['products_options_id'], $attributes[$product['products_id']]['options_list'])) {
                return $this->product_option_values[$combinations[$this->product_options[$this->feedData['FEED_FIELD_COUPON_2']]['products_options_id']]];
            }
        }
        if ( $this->feedData['FEED_ATTRIBUTES_GENDER_3'] != '' ){
            return $this->feedData['FEED_ATTRIBUTES_GENDER_3'] ;
        }

        return '';
    }

    public function getGender($product,$combinations,$attributes){
        if($this->feedData['FEED_FIELD_GENDER_1'] != 'N' and is_string($this->feedData['FEED_FIELD_GENDER_1'])) {
            $temp = explode(';', $this->feedData['FEED_FIELD_GENDER_1']);
            $temp = $temp[1];
            if ($product[$temp]) {
                return $product[$temp];
            }
        }
        if ($this->feedData['FEED_ATTRIBUTES_GENDER_2'] != 'N' ) {
            if (array_key_exists($this->product_options[$this->feedData['FEED_ATTRIBUTES_GENDER_2']]['products_options_id'], $attributes[$product['products_id']]['options_list'])) {
                return $this->product_option_values[$combinations[$this->product_options[$this->feedData['FEED_ATTRIBUTES_GENDER_2']]['products_options_id']]];
            }
        }
        if ( $this->feedData['FEED_ATTRIBUTES_GENDER_3'] != '' ){
            return $this->feedData['FEED_ATTRIBUTES_GENDER_3'] ;
        }

        return '';
    }

    public function getSize($product,$combinations,$attributes){
        if($this->feedData['FEED_FIELD_SIZE_1'] != 'N' and is_string($this->feedData['FEED_FIELD_SIZE_1'])){
            $temp = explode(';',$this->feedData['FEED_FIELD_SIZE_1']);
            $temp = $temp[1] ;
            if($product[$temp]){

                return  $product[$temp] ;
            }
        }
        if ($this->feedData['FEED_ATTRIBUTES_SIZE_2'] != 'N' ){
            if( array_key_exists($this->product_options[$this->feedData['FEED_ATTRIBUTES_SIZE_2']]['products_options_id'] , $attributes[$product['products_id']]['options_list'] )){

                return $this->product_option_values[$combinations[$this->product_options[$this->feedData['FEED_ATTRIBUTES_SIZE_2']]['products_options_id']]] ;
            }
        }
        if ( $this->feedData['FEED_ATTRIBUTES_SIZE_3'] != '' ){

            return $this->feedData['FEED_ATTRIBUTES_SIZE_3'] ;
        }

        return '';
    }

    public function getColor($product,$combinations,$attributes){

        if($this->feedData['FEED_FIELD_COLOR_1'] != 'N' and is_string($this->feedData['FEED_FIELD_COLOR_1'])){
            $temp = explode(';',$this->feedData['FEED_FIELD_COLOR_1']);
            $temp = $temp[1] ;
            if($product[$temp]){

                return  $product[$temp] ;
            }
        }
        if ($this->feedData['FEED_ATTRIBUTES_COLOR_2'] != 'N' ){
                //var_dump( $attributes[$product['products_id']]['options_list']);die;
            if( array_key_exists($this->product_options[$this->feedData['FEED_ATTRIBUTES_COLOR_2']]['products_options_id'] , $attributes[$product['products_id']]['options_list'] )){

                return $this->product_option_values[$combinations[$this->product_options[$this->feedData['FEED_ATTRIBUTES_COLOR_2']]['products_options_id']]] ;
            }
        }
        if ( $this->feedData['FEED_ATTRIBUTES_COLOR_3'] != '' ){

            return $this->feedData['FEED_ATTRIBUTES_COLOR_3'] ;
        }



        return '';
    }

    public function getMaterial($product,$combinations,$attributes){
        if($this->feedData['FEED_FIELD_MATERIAL_1'] != 'N' and is_string($this->feedData['FEED_FIELD_MATERIAL_1'])) {
            $temp = explode(';', $this->feedData['FEED_FIELD_MATERIAL_1']);
            $temp = $temp[1];
            if ($product[$temp]) {

                return $product[$temp];
            }
        }
        if ($this->feedData['FEED_ATTRIBUTES_MATERIAL_2'] != 'N' ) {
            if (array_key_exists($this->product_options[$this->feedData['FEED_ATTRIBUTES_MATERIAL_2']]['products_options_id'], $attributes[$product['products_id']]['options_list'])) {

                return $this->product_option_values[$combinations[$this->product_options[$this->feedData['FEED_ATTRIBUTES_MATERIAL_2']]['products_options_id']]];
            }
        }
        if ( $this->feedData['FEED_ATTRIBUTES_MATERIAL_3'] != '' ){

            return $this->feedData['FEED_ATTRIBUTES_MATERIAL_3'] ;
        }

        return '';
    }

    public function getPacketSize($product,$combinations,$attributes){
        if($this->feedData['FEED_FIELD_MATERIAL_1'] != 'N' and is_string($this->feedData['FEED_FIELD_MATERIAL_1'])) {
            $temp = explode(';', $this->feedData['FEED_FIELD_MATERIAL_1']);
            $temp = $temp[1];
            if ($product[$temp]) {

                return $product[$temp];
            }
        }
        if ($this->feedData['FEED_ATTRIBUTES_MATERIAL_2'] != 'N' ) {
            if (array_key_exists($this->product_options[$this->feedData['FEED_ATTRIBUTES_MATERIAL_2']]['products_options_id'], $attributes[$product['products_id']]['options_list'])) {

                return $this->product_option_values[$combinations[$this->product_options[$this->feedData['FEED_ATTRIBUTES_MATERIAL_2']]['products_options_id']]];
            }
        }
        if( $this->feedData['FEED_FIELD_PACKET_SIZE_LENGTH'] !='' and
                    $this->feedData['FEED_FIELD_PACKET_SIZE_WIDTH']  !='' and
                    $this->feedData['FEED_FIELD_PACKET_SIZE_HEIGHT'] !='' ){

            return $this->feedData['FEED_FIELD_PACKET_SIZE_LENGTH'].'x'.
                    $this->feedData['FEED_FIELD_PACKET_SIZE_WIDTH'] . 'x'.
                    $this->feedData['FEED_FIELD_PACKET_SIZE_HEIGHT'] . ' cm';
        }

        return '';
    }

    public function getDeliveryTime($product,$combinations,$attributes){
        if($key = $this->feedData['FEED_DTIME_1'] != 'N' ){
            $temp = explode(';',$this->feedData['FEED_DTIME_1']);
            $temp = $temp[1] ;
            $result = '';
            if($product[$temp] != null){
                return  $product[$temp] ;
            }
        }

        if ($this->feedData['FEED_DTIME_2'] != 'N' ){
            if( array_key_exists($this->product_options[$this->feedData['FEED_DTIME_2']]['products_options_id'] , $attributes[$product['products_id']]['options_list'] )){
                return $this->product_option_values[$combinations[$this->product_options[$this->feedData['FEED_DTIME_2']]['products_options_id']]] ;
            }
        }

        return $this->getInfoFromDeliveringForm() ;
    }

    public function getInfoFromDeliveringForm(){
        $from = $to = $type = $result = null ;
        if(isset($this->feedData['FEED_DTIME_FROM'])){
            $from  = $this->feedData['FEED_DTIME_FROM'];
        }
        if(isset($this->feedData['FEED_DTIME_TO'])){
            $to = $this->feedData['FEED_DTIME_TO'];
        }
        if(isset($this->feedData['FEED_DTIME_TYPE'])){
            $type = $this->feedData['FEED_DTIME_TYPE'];
        }
        if($from)
            $result = $from.'_';
        if($to)
            $result .= $to.'_';
        if(($type and $to) or ($type and $from)){
            $result .= $type;
        } else {
            $result = '';
        }

        return $result ;
    }

    public function getShipping($product,$combinations,$attributes){
        if ($this->feedData['FEED_FIELD_SHIPPING_COST_1'] != 'N' and is_string($this->feedData['FEED_FIELD_SHIPPING_COST_1'])) {
            $temp = explode(';', $this->feedData['FEED_FIELD_SHIPPING_COST_1']);
            $temp = $temp[1];
            if ($product[$temp]) {
                return $product[$temp];
            }
        }
        if ($this->feedData['FEED_FIELD_SHIPPING_COST_2'] != 'N') {
            if (array_key_exists($this->product_options[$this->feedData['FEED_FIELD_SHIPPING_COST_2']]['products_options_id'], $attributes[$product['products_id']]['options_list'])) {
                return $this->product_option_values[$combinations[$this->product_options[$this->feedData['FEED_FIELD_SHIPPING_COST_2']]['products_options_id']]];
            }
        }
        if ($this->feedData['FEED_FIELD_SHIPPING_COST_3'] != '') {
            return $this->feedData['FEED_FIELD_SHIPPING_COST_3'];
        }

        return '';
    }

    public function getShippingAddition($product){
        if($this->feedData['FEED_SHIPPING_ADDITION_1'] != 'N' and is_string($this->feedData['FEED_SHIPPING_ADDITION_1'])){
            $temp = explode(';',$this->feedData['FEED_SHIPPING_ADDITION_1']);
            $temp = $temp[1] ;
            if($product[$temp]){
                return  $product[$temp] ;
            } elseif ($this->feedData['FEED_SHIPPING_ADDITION_2'] ){
                return $this->feedData['FEED_SHIPPING_ADDITION_2'];
            }
        } elseif ($this->feedData['FEED_SHIPPING_ADDITION_2'] ){
            return $this->feedData['FEED_SHIPPING_ADDITION_2'];
        }
        return '';
    }

    public function getShippingPaypalCost($product){
        if($this->feedData['FEED_SHIPPING_PAYPAL_OST_1'] != 'N' and is_string($this->feedData['FEED_SHIPPING_PAYPAL_OST_1'])){
            $temp = explode(';',$this->feedData['FEED_SHIPPING_PAYPAL_OST_1']);
            $temp = $temp[1] ;
            if($product[$temp]){
                return  $product[$temp] ;
            } elseif ($this->feedData['FEED_SHIPPING_PAYPAL_OST_2'] ){
                return $this->feedData['FEED_SHIPPING_PAYPAL_OST_2'];
            }
        } elseif ($this->feedData['FEED_SHIPPING_PAYPAL_OST_2'] ){
            return $this->feedData['FEED_SHIPPING_PAYPAL_OST_2'];
        }
        return '';
    }

    public function getShippingCode($product){
        if($this->feedData['FEED_SHIPPING_COD_1'] != 'N' and is_string($this->feedData['FEED_SHIPPING_COD_1'])){
            $temp = explode(';',$this->feedData['FEED_SHIPPING_COD_1']);
            $temp = $temp[1] ;
            if($product[$temp]){
                return  $product[$temp] ;
            } elseif ($this->feedData['FEED_SHIPPING_COD_2'] ){
                return $this->feedData['FEED_SHIPPING_COD_2'];
            }
        } elseif ($this->feedData['FEED_SHIPPING_COD_2'] ){
            return $this->feedData['FEED_SHIPPING_COD_2'];
        }
        return '';
    }

    public function getShippingCredit($product){
        if($this->feedData['FEED_SHIPPING_CREDIT_1'] != 'N' and is_string($this->feedData['FEED_SHIPPING_CREDIT_1'])){
            $temp = explode(';',$this->feedData['FEED_SHIPPING_CREDIT_1']);
            $temp = $temp[1] ;
            if($product[$temp]){
                return  $product[$temp] ;
            } elseif ($this->feedData['FEED_SHIPPING_CREDIT_2'] ){
                return $this->feedData['FEED_SHIPPING_CREDIT_2'];
            }
        } elseif ($this->feedData['FEED_SHIPPING_CREDIT_2'] ){
            return $this->feedData['FEED_SHIPPING_CREDIT_2'];
        }
        return '';
    }

    public function getShippingPaypal($product){
        if($this->feedData['FEED_SHIPPING_PAYPAL_1'] != 'N' and is_string($this->feedData['FEED_SHIPPING_PAYPAL_1'])){
            $temp = explode(';',$this->feedData['FEED_SHIPPING_PAYPAL_1']);
            $temp = $temp[1] ;
            if($product[$temp]){
                return  $product[$temp] ;
            } elseif ($this->feedData['FEED_SHIPPING_PAYPAL_2'] ){
                return $this->feedData['FEED_SHIPPING_PAYPAL_2'];
            }
        } elseif ($this->feedData['FEED_SHIPPING_PAYPAL_2'] ){
            return $this->feedData['FEED_SHIPPING_PAYPAL_2'];
        }
        return '';
    }

    public function getShippingTransfer($product){
        if($this->feedData['FEED_SHIPPING_TRANSFER_1'] != 'N' and is_string($this->feedData['FEED_SHIPPING_TRANSFER_1'])){
            $temp = explode(';',$this->feedData['FEED_SHIPPING_TRANSFER_1']);
            $temp = $temp[1] ;
            if($product[$temp]){
                return  $product[$temp] ;
            } elseif ($this->feedData['FEED_SHIPPING_TRANSFER_2'] ){
                return $this->feedData['FEED_SHIPPING_TRANSFER_2'];
            }
        } elseif ($this->feedData['FEED_SHIPPING_TRANSFER_2'] ){
            return $this->feedData['FEED_SHIPPING_TRANSFER_2'];
        }
        return '';
    }

    public function getShippingDebit($product){
        if($this->feedData['FEED_SHIPPING_DEBIT_1'] != 'N' and is_string($this->feedData['FEED_SHIPPING_DEBIT_1'])){
            $temp = explode(';',$this->feedData['FEED_SHIPPING_DEBIT_1']);
            $temp = $temp[1] ;
            if($product[$temp]){
                return  $product[$temp] ;
            } elseif ($this->feedData['FEED_SHIPPING_DEBIT_2'] ){
                return $this->feedData['FEED_SHIPPING_DEBIT_2'];
            }
        } elseif ($this->feedData['FEED_SHIPPING_DEBIT_2'] ){
            return $this->feedData['FEED_SHIPPING_DEBIT_2'];
        }
        return '';
    }

    public function getShippingAccount($product){
        if($this->feedData['FEED_SHIPPING_ACCOUNT_1'] != 'N' and is_string($this->feedData['FEED_SHIPPING_ACCOUNT_1'])){
            $temp = explode(';',$this->feedData['FEED_SHIPPING_ACCOUNT_1']);
            $temp = $temp[1] ;
            if($product[$temp]){
                return  $product[$temp] ;
            } elseif ($this->feedData['FEED_SHIPPING_ACCOUNT_2'] ){
                return $this->feedData['FEED_SHIPPING_ACCOUNT_2'];
            }
        } elseif ($this->feedData['FEED_SHIPPING_ACCOUNT_2'] ){
            return $this->feedData['FEED_SHIPPING_ACCOUNT_2'];
        }
        return '';
    }

    public function getShippingMoneybookers($product){
        if($this->feedData['FEED_SHIPPING_MONEYBOOKERS_1'] != 'N' and is_string($this->feedData['FEED_SHIPPING_MONEYBOOKERS_1'])){
            $temp = explode(';',$this->feedData['FEED_SHIPPING_MONEYBOOKERS_1']);
            $temp = $temp[1] ;
            if($product[$temp]){
                return  $product[$temp] ;
            } elseif ($this->feedData['FEED_SHIPPING_MONEYBOOKERS_2'] ){
                return $this->feedData['FEED_SHIPPING_MONEYBOOKERS_2'];
            }
        } elseif ($this->feedData['FEED_SHIPPING_MONEYBOOKERS_2'] ){
            return $this->feedData['FEED_SHIPPING_MONEYBOOKERS_2'];
        }
        return '';
    }

    public function getShippingGiropay($product){
        if($this->feedData['FEED_SHIPPING_GIROPAY_1'] != 'N' and is_string($this->feedData['FEED_SHIPPING_GIROPAY_1'])){
            $temp = explode(';',$this->feedData['FEED_SHIPPING_GIROPAY_1']);
            $temp = $temp[1] ;
            if($product[$temp]){
                return  $product[$temp] ;
            } elseif ($this->feedData['FEED_SHIPPING_GIROPAY_2'] ){
                return $this->feedData['FEED_SHIPPING_GIROPAY_2'];
            }
        } elseif ($this->feedData['FEED_SHIPPING_GIROPAY_2'] ){
            return $this->feedData['FEED_SHIPPING_GIROPAY_2'];
        }
        return '';
    }

    public function getShippingComment(){
        if(isset($this->feedData['FEED_SHIPPING_COMMENT'])){
            return $this->feedData['FEED_SHIPPING_COMMENT'];
        }
        return '';
    }

    public function getShippingClickBy($product){
        if($this->feedData['FEED_SHIPPING_CLICK_BUY_1'] != 'N' and is_string($this->feedData['FEED_SHIPPING_CLICK_BUY_1'])){
            $temp = explode(';',$this->feedData['FEED_SHIPPING_CLICK_BUY_1']);
            $temp = $temp[1] ;
            if($product[$temp]){
                return  $product[$temp] ;
            } elseif ($this->feedData['FEED_SHIPPING_CLICK_BUY_2'] ){
                return $this->feedData['FEED_SHIPPING_CLICK_BUY_2'];
            }
        } elseif ($this->feedData['FEED_SHIPPING_CLICK_BUY_2'] ){
            return $this->feedData['FEED_SHIPPING_CLICK_BUY_2'];
        }
        return '';
    }

    public function getCategory($product){
        $query = '
                    select
                        ptc.categories_id as categories_id

                    from '.TABLE_PRODUCTS_TO_CATEGORIES.' ptc
                    where ptc.products_id='.$product["products_id"] ;

        $db = $GLOBALS['db'];
        $temp = $this->dataFetch($db->Execute($query));
        if(!$temp){
            var_dump($product);die;
        }
        $buff = array();
        $categories = array();
        foreach ($temp as $item) {
            $buff[$item['categories_id']] = $item;
            $categories[] = $this->productsCategory[$item['categories_id']];
        }

        return $this->getCategoriesParent($categories, null);
    }

    public function getCategoriesParent($categories,$result = null){
        $temp = array();
        $response = 1;
        foreach ($categories as $item) {
            if(!is_array($item)){
                $temp[0] =  $categories ;
                $categories = $temp ;
                break;
            }
        }
        foreach ($categories as $category) {
            if( $result == null ){
                if($category['parent_id'] == 0){
                    $response = $category['categories_name'];
                } else {
                    $response = $this->getCategoriesParent($this->productsCategory[$category['parent_id']], $category['categories_name']);
                }
            } else {
                if($category['parent_id'] == 0){
                    $response = $category['categories_name'].'|'.$result;
                } else {
                    $response = $this->getCategoriesParent($this->productsCategory[$category['parent_id']], $category['categories_name'].'|'.$result);
                }
            }
        }

        return $response ;
    }

    public function getProductTax($product){
        $a = zen_get_tax_rate($product['tax_class_id'], $this->taxZone['zone_country_id'], $this->taxZone['zone_id']);
        if(!$a){
            //return 1 ; //return value from plogin from
        }
        return $a;
    }

    public function getProductVariants($attributes,$product,$combinations){

        $temp = array();

        foreach ($combinations as $key => $value) {
            $temp[] = ($this->product_options[$key]['products_options_name']);
        }
        return  implode('|',$temp);
    }

    public function getModelOwn($product,$combinations=null){
        $temp = $product['products_id'];
        $buff = array();
        if($combinations){
            foreach ($combinations as $key => $value) {
                $buff[] = $key.'-'.$value;
            }
            $temp =  $temp.'_'.implode('_',$buff);
        }

        return $temp;
    }


}

