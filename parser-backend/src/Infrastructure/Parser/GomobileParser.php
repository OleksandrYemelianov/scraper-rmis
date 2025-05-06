<?php

namespace App\Infrastructure\Parser;

class GomobileParser extends AppParser
{
    private const BATCH = 10;
    private const string API_URL = 'https://api.gomobile.co.il/api/products';

    private $files = [];

    protected function getBatch(): int
    {
        return self::BATCH;
    }

    protected function getProductData(array $pre_product, array $category_setting): array
    {
        $product_data = [];
        $this->files = [];

        // name
        $product_data['title'] = $pre_product['name'];

        // description
        if (!empty($profile['parseDesc']) && !empty($pre_product['infos']['technical_specs'])) {
             $tech_desc = $this->getTechSpec($pre_product['infos']['technical_specs']);
            $product_data['descriptionHtml'] = empty($tech_desc) ? $pre_product['description'] : $tech_desc;
        }

        // variants
        if ((bool)$pre_product['hasVariations']) {
            $options = $this->getProductOptions($pre_product);
            if (!empty($options)) {
                $product_data['productOptions'] = $options['options'];
                $options_key = $options['options_key'];
                $product_data['variants'] = $this->getVariants(
                    $pre_product,
                    $options_key,
                    $category_setting['id']
                );

                $product_data['productOptions'] = $this->getCorrectProductOptions($product_data['productOptions'], $product_data['variants']);
            }    
        }

        if (!empty($this->files)) {
            $product_data['files'] = $this->files;
        }    

        // metafields
        $product_data['metafields'] = [
            [
                'namespace' => 'dialer',
                'key' => 'dialer_name',
                'value' => $this->profile['diler'],
                'type' => 'single_line_text_field'
            ],
            [
                'namespace' => 'dialer',
                'key' => 'dialer_sku',
                'value' => $pre_product['sku'],
                'type' => 'single_line_text_field'
            ]
        ];

        return $product_data;
    }

    // gomobile иногда отдает несоотвествие между productOptions и variants
    private function getCorrectProductOptions(array $options, array $variants): array
    {
        foreach ($variants as $variant) {
            foreach ($variant['optionValues'] as $option) {
                $this->checkOption($options, $option);
            }
        }

        return $options;
    }

    private function checkOption(array &$options, array $option): void
    {
        foreach ($options as &$item) {
            if ($item['name'] == $option['optionName']) {
                $exist = false;
                foreach ($item['values'] as &$value) {
                    if ($value['name'] == $option['name']) {
                        $exist = true;
                    } elseif (strtoupper($value['name']) == strtoupper($option['name'])) {
                        $value['name'] = strtoupper($option['name']);
                        $exist = true;
                    }
                }

                if (!$exist) {
                    $item['values'][] = ['name' => $option['name']];
                }    
            }    
        }
    }

    private function getProductOptions($pre_product): array
    {
        $res = [];
        if (empty($pre_product['variationOptions'])) {
            return $res;
        }

        foreach ($pre_product['variationOptions'] as $option) {
            $item = [
                'name' => $option['label'],
            ];
            foreach ($option['options'] as $name) {
                $item['values'][] = [
                    'name' => $this->nameFormat($name),
                ];
            }
            $res['options'][] = $item;

            $res['options_key'][$option['key']] = $option['label'];
        }

        return $res;
    }

    private function getVariants(array $pre_product, array $options_key, int $category_id): array
    {
        $variants = [];
        if (empty($pre_product['variations']) || empty($options_key)) {
            return $variants;
        }

        $i = 1;
        foreach ($pre_product['variations'] as $_variant) {
            $variant = [];
            foreach ($_variant['attribute'] as $key => $item) {
                $variant['optionValues'][] = [
                    'optionName' => empty( $options_key[$key] ) ? $this->nameFormat($key) : $options_key[$key],
                    'name' => $item['value']
                ];
            }
            $variant['price'] = $this->calculatePrice($pre_product['price']['price'], $category_id);
            //$variant['compareAtPrice'] = 99.99;
            $variant['sku'] = $this->getSku();
            $variant['inventoryQuantities'][] = [
                'quantity' => $this->getStock($_variant['stock']),
                'name' => 'available'
            ];

            $file = [
                'originalSource' => $_variant['image'],
                'contentType' => 'IMAGE'
            ];
            $variant['file'] = $file;
            $this->files[] = $file;

            // metafields
            $variant['metafields'] = [
                [
                    'namespace' => 'dialer',
                    'key' => 'dialer_variant_sku',
                    'value' => $_variant['sku'],
                    'type' => 'single_line_text_field'
                ]
            ];

            $variants[] = $variant;
        }

        return $variants;
    }

    protected function getPreProductsByCategory($category_info): array
    {
        if (empty($category_info['url'])) {
            return [];
        }    
        $tmp = explode('/', urldecode($category_info['url']));
        $category_slug = $tmp[4] ?: '';

        $response = json_decode(file_get_contents(self::API_URL . '?category_slug=' . $category_slug), true);
        $products = $response['status'] == 'success' ? $response['products'] : [];
        if (!empty($products)) {
            foreach ($products as &$product) {
                $product = $this->setParseCategoryId($product, $category_info['id']);
            }
        }    
        return $products;
    }

    private function nameFormat(string $name): string
    {
        return ucwords(str_replace("-", " ",$name));
    }

    private function getProduct($product_ids): array
    {
        $response = json_decode(
            file_get_contents(self::API_URL . '?id[]=' . join('&id[]=', $product_ids)), true
        );
        return $response['status'] == 'success' ? $response['products'] : [];
    }

    private function getStock($stocks): int
    {
        $in_stock = 0;
        foreach ($stocks as $stock) {
            if ($stock['stockStatus'] == 'instock') {
                $in_stock = 99;
            }
        }

        return $in_stock;
    }


    private function getTechSpec($html)
    {
        $tmp = explode("\r\n", $html);
        $output = '';
        $strong = false;
        foreach ($tmp as $item) {
            $item = preg_replace('/<\/?p[^>]*>|<br\s*\/?>/i', '', $item);
            if (strpos($item,'<strong>') !== false) {
                if (!empty($output)) {
                    $output .= '</ul>';
                }
                $item = str_replace('<strong>', '<i><b>', $item);
                $item = str_replace('</strong>', '</i></b><ul>', $item);
                $strong = true;
            } else {
                $tmp2 = explode(':', $item);
                $li = $strong ? '<li class="first">' : '<li>';
                $tmp2[0] = str_replace('•', '', $tmp2[0]);
                if (count($tmp2) > 1) {
                    $item = $li . '<b>' . $tmp2[0] . '</b><span class="he_false">' . $tmp2[1] . '</span></li>';
                } else {
                    $item = $li . '<span class="he_false">' . $tmp2[0] . '</span></li>';
                }

                $strong = false;
            }
            $output .= $item;
        }

        return $output;
    }
}
