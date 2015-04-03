<?php

class FeedConnector implements FeedPlugin {

    protected $productsWithAttributes;
    protected $order;
    protected $config;

    /**
     * constructor caller is forwarded
     *
     * @param Feed $container
     */
    public function __construct(Feed $container)
    {
        $this->config = new FeedConfig();
    }

    /**
     * Returns APIUsername
     * @return string
     */
    public function getApiUsername()
    {
        return $this->config->getConfig('FEED_USER');
    }

    /**
     * Return APIPassword
     * @return string
     */
    public function getApiPassword()
    {
        return $this->config->getConfig('FEED_PASS');
    }

    /**
     * Returns APISecret code
     * @return string
     */
    public function getApiSecret()
    {
       return $this->config->getConfig('FEED_SECRET');
    }

    /**
     * Returns identifyer (oxid, magento, opencart)
     * @return string
     */
    public function getShopName()
    {
        return 'zencart';//$this->config->getConfig('STORE_NAME');
    }

    /**
     * Returns possible shop configuration option for different channels
     * @return stdClass
     */
    public function getShopConfig()
    {
        /*$currency = null;
        $temp1 = array();
        $temp2 = array();
        $oReturn = new stdClass();
		//$availability = $this->config->getShopAvailabilityConfig();
		$availability = $this->config->__getShopAvailabilityConfig();
        foreach ($availability->values as $item) {
            $temp2[$item->key] = $item->title ;
        }
        $oReturn->availability = $temp2 ;
        $oReturn->language = $this->config->getShopLanguageConfig();
        $currency = $this->config->getShopCurrencyConfig();
        foreach ($currency->values as $key=>$value) {
            $temp1[$value->key] = $value->title;
        }
        $oReturn->currency = $temp1;

        return $oReturn;*/

        $shopConfig = new stdClass();
        $shopConfig->language = $this->config->getShopLanguageConfig();
        $shopConfig->currency = $this->config->getShopCurrencyConfig();
        $shopConfig->status = $this->config->getShopCondition();

        return $shopConfig;
    }



    /**
     * Generates and returns the array of datafeed
     *
     * @param stdClass $queryParameters
     * @param array $fieldMap
     * @return stdClass
     */
    public function getFeed(stdClass $queryParameters, array $fieldMap)
    {
        set_time_limit(0);
        $this->config->iniParameters();
        $limit        = 10;
        $offset       = 0;
        $tempContents = array();

        //save sessions cart contents
        if( $_SESSION['cart']->contents ) {
            $tempContents = $_SESSION['cart']->contents;
            $_SESSION['cart']->reset();
        }

        header('Content-Encoding: UTF-8');
        header("Content-type: text/csv; charset=UTF-8");
        header('Content-Disposition: attachment; filename=feed.csv');
        mb_internal_encoding("UTF-8");


        $csv_file = fopen("php://output", 'w+');
        if(!$csv_file) {
            echo 'File Error';
            exit();
        }

        fputcsv($csv_file, array_keys($fieldMap), ';', '"');
        $shopConfig = $this->getShopConfig();
        do{

            $products   = $this->config->getProducts($limit, $offset,$queryParameters);
            $attributes = $this->config->getProductsAttr();
            $count = 0;

            foreach ($products as $product) {
                $this->config->uploadCSVfileWithCombinations($csv_file,$product,$attributes,$fieldMap, $shopConfig,$queryParameters);
                flush();

                ++$count;
            }
            $offset += $limit;
        } while ($count == $limit);

        fclose($csv_file);
        if( $tempContents ) {
            $_SESSION['cart']->contents = $tempContents;
        }

    }

    /**
     * Returns the URL where to get generated DataFeed
     *
     * @param stdClass $queryParameters
     * @param array $fieldMap
     * @return string
     */
    public function getFeedUrl(stdClass $queryParameters, array $fieldMap = null)
    {
        // TODO: Implement getFeedUrl() method.
    }

    /**
     * Generates and returns the delta changes array
     *
     * @param stdClass $queryParameters
     * @param array $fieldMap
     * @param int $deltaTimestamp
     * @return stdClass
     */
    public function getDelta(stdClass $queryParameters, array $fieldMap, int $deltaTimestamp)
    {
        // TODO: Implement getDelta() method.
    }

    /**
     * Generates and returns the orders
     *
     * @param int $deltaTimestamp
     * @return stdClass
     */
    public function getOrders(int $deltaTimestamp)
    {
        // TODO: Implement getOrders() method.
    }

    /**
     * Returns the url from where to get the article
     *
     * @param int $deltaTimestamp
     * @return string
     */
    public function getOrdersUrl(int $deltaTimestamp)
    {
        // TODO: Implement getOrdersUrl() method.
    }

    /**
     * Returns the bridge URL throw the Feed is communicating with shop.
     *
     * @return string
     */
    public function getBridgeUrl()
    {
        return 'http://'.$_SERVER['SERVER_NAME'].'/feed.php';
    }

    /**
     * Returns the bridge URL parameters the Feed is communicating with shop.
     *
     * @return string
     */
    public function getUrlParameters()
    {
        // TODO: Implement getUrlParameters() method.
    }

    /**
     * Returns posible shop fields configuration throw the Feed gets csv fields
     * @return stdClass
     */
    public function getShopFields()
    {
        return array_merge(FeedConfig::$gReturn, $this->config->getQueryFields());
    }

    /**
     * Returns product info
     *
     * @param stdClass $queryParameters
     * @param array $fieldMap
     * @param string $id
     * @return mixed
     */
    public function getProductInfo(stdClass $queryParameters, array $fieldMap, $id)
    {
        $this->config->iniParameters();
		$products_id = strtok($id,"_");
        $this->config->getProductsAttributes(array(), array($products_id));
        $attributes = array();

        $product = $this->config->getProductsResource($queryParameters, 0, 0, array($products_id));

        if($product[0]) {
            if( $this->config->productAttributes[$products_id] && strpos($id, '_')) {
                $attributes = $this->config->productAttributes[$products_id];
                $this->config->productAttributes = array();
                $this->config->productAttributes[$products_id] = $attributes;
                $product[0]['attributes_combo'] = $this->config->getAttributes();
                $product[0]['attributes_combo'] = $product[0]['attributes_combo'][0];
            }

            $article = $this->config->getFeedRow($fieldMap,$product[0],$queryParameters->lang,$queryParameters->currency);
			$article['ModelOwn'] = $id;
			//$temp = 0;

            foreach($product[0]['attributes_combo'] as $combo) {
                if( $products_id.'_'.implode('_', $combo) == $id ) {

                    $article['ModelOwn'] = $id;
					$x = $product[0]['price'];
					$article['Productsprice_brut'] = round(($x) * $product[0]['currencies_value'], $product[0]['currencies_decimal_places']);

                    foreach($combo as $attribute) {
                        switch($attributes[$attribute]['weight_prefix']) {
                            case '+':
                                $article['Weight'] += $attributes[$attribute]['products_attributes_weight'];
                                break;
                            case '-':
                                $article['Weight'] -= $attributes[$attribute]['products_attributes_weight'];
                                break;
                            default:
                                $article['Weight'] += $attributes[$attribute]['products_attributes_weight'];
                                break;
                        }

                        if($article['Productspecial']) {

                            switch($attributes[$attribute]['price_prefix']) {
                                case '+':
                                    $article['Productsprice_brut'] += $attributes[$attribute]['options_values_price']*$product[0]['currencies_value'];
                                    break;
                                case '-':
                                    $article['Productsprice_brut'] -= $attributes[$attribute]['options_values_price']*$product[0]['currencies_value'];
                                    break;
                                default:
                                    $temp = $attributes[$attribute]['options_values_price']*$product[0]['currencies_value'];
                                    break;
                            }
                        } else {

                            switch($attributes[$attribute]['price_prefix']) {
                                case '+':
                                    $article['Productsprice_brut'] += $attributes[$attribute]['options_values_price']*$product[0]['currencies_value'];
                                    break;
                                case '-':
                                    $article['Productsprice_brut'] -= $attributes[$attribute]['options_values_price']*$product[0]['currencies_value'];
                                    break;
                                default:
                                    $temp = $attributes[$attribute]['options_values_price']*$product[0]['currencies_value'];
                                    break;
                            }
                        }
                    }

					if(isset($temp)) {
						$article['Productsprice_brut'] += $temp;
					}

					//generating Products Variant
					$varGroups = array();
					$temp = explode("_", $article['ModelOwn']);
					unset($temp[0]);
					foreach($temp as $item) {
						$varGroups[$attributes[$item]['products_options_name']] = true;
					}

					foreach($varGroups as $key => $group) {
						$article['ProductsVariant'] .= "_$key";
					}
					$article['ProductsVariant'] = substr($article['ProductsVariant'], 1);
					//end generate

                    break;
                }
            }
        } else {
            header('HTTP/1.0 404 Not Found');
            return false;
        }

        if(strpos($id, '_') && !$article['ProductsVariant']) {
            header('HTTP/1.0 404 Not Found');
            echo '   No product with id '.$id;
            return false;
        } else {
            print_r($article);
        }

        return $article;
    }

    /**
     * @param stdClass $queryParameters
     * @param $id
     * @return mixed
     */
    public function getOrderProducts(stdClass $queryParameters, $id)
    {
		$products = $this->config->getOrdersProducts($queryParameters->currency, $id);

		return $products;
	}

    /**
     * @return mixed
     */
    public function getFeatures()
    {
        $return = array (
            'getShopName',
            'getConfig',
            'getFeed',
            'getFields',
            'getBridgeUrl',
//            'getFeedUrl',
//            'getDelta',
//            'getOrders',
//            'getOrdersUrl',
            'getProduct',
            'getOrderProducts',
        );
        return $return;
    }
}