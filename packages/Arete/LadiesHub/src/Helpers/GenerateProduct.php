<?php

namespace Arete\LadiesHub\Helpers;

use Webkul\Attribute\Models\Attribute;
use Webkul\Attribute\Models\AttributeOption;
use Webkul\Product\Repositories\ProductRepository;
use Webkul\Attribute\Repositories\AttributeFamilyRepository;
use Illuminate\Support\Str;

/**
 * Class GenerateProduct
 *
 * @package Arete\LadiesHub\Helpers
 */
class GenerateProduct
{
    /**
     * Product Repository instance
     * 
     * @var \Webkul\Product\Repositories\ProductRepository
     */
    protected $productRepository;

    /**
     * AttributeFamily Repository instance
     * 
     * @var \Webkul\Product\Repositories\AttributeFamilyRepository
     */
    protected $attributeFamilyRepository;

    /**
     * Product Attribute Types
     * 
     * @var array
     */
    protected $types;

    /**
     * Create a new helper instance.
     *
     * @param  \Webkul\Product\Repositories\ProductRepository  $productImage
     * @param  \Webkul\Product\Repositories\AttributeFamilyRepository  $productImage
     * @return void
     */
    public function __construct(
        ProductRepository $productRepository,
        AttributeFamilyRepository $attributeFamilyRepository
    )
    {
        $this->productRepository = $productRepository;

        $this->attributeFamilyRepository = $attributeFamilyRepository;

        $this->types = [
            'text',
            'textarea',
            'boolean',
            'select',
            'multiselect',
            'datetime',
            'date',
            'price',
            'image',
            'file',
            'checkbox',
        ];
    }

    /**
     * This brand option needs to be available so that the generated product
     * can be linked to the order_brands table after checkout.
     * 
     * @return void
     */
    public function generateDemoBrand()
    {
        $brand = Attribute::where(['code' => 'brand'])->first();

        if (! AttributeOption::where(['attribute_id' => $brand->id])->exists()) {

            AttributeOption::create([
                'admin_name'   => 'Ladies Hub Joy Gifts',
                'attribute_id' => $brand->id,
            ]);
        }
    }

    /**
     * @return mixed
     */
    public function create()
    {
        $attributeFamily = $this->attributeFamilyRepository->findWhere([
            'code' => 'default',
        ])->first();

        $attributes = $this->getFamilyAttributes($attributeFamily);


        $faker = \Faker\Factory::create();

        $sku = strtolower($faker->bothify('??#####???'));
        $data['sku'] = $sku;
        $data['attribute_family_id'] = $attributeFamily->id;
        $data['type'] = 'simple';

        $product = $this->productRepository->create($data);

        unset($data);

        $date = today();
        $specialFrom = $date->toDateString();
        $specialTo = $date->addDays(7)->toDateString();

        foreach ($attributes as $attribute) {
            if ($attribute->type == 'text') {
                if ($attribute->code == 'width'
                    || $attribute->code == 'height'
                    || $attribute->code == 'depth'
                    || $attribute->code == 'weight'
                ) {
                    $data[$attribute->code] = $faker->randomNumber(3);
                } elseif ($attribute->code == 'url_key') {
                    $data[$attribute->code] = $sku;
                } elseif ($attribute->code =='product_number') {
                    $data[$attribute->code] = $faker->randomNumber(5);
                } elseif ($attribute->code =='name') {
                    $data[$attribute->code] = ucfirst($faker->words(rand(1,4),true));
                } elseif ($attribute->code != 'sku') {
                    $data[$attribute->code] = $faker->name;
                } else {
                    $data[$attribute->code] = $sku;
                }
            } elseif ($attribute->type == 'textarea') {
                $data[$attribute->code] = $faker->text;

                if ($attribute->code == 'description' || $attribute->code == 'short_description') {
                    $data[$attribute->code] = '<p>' . $data[$attribute->code] . '</p>';
                }
            } elseif ($attribute->type == 'boolean') {
                $data[$attribute->code] = $faker->boolean;
            } elseif ($attribute->type == 'price') {
                $data[$attribute->code] = rand(5,200);
            } elseif ($attribute->type == 'datetime') {
                $data[$attribute->code] = $date->toDateTimeString();
            } elseif ($attribute->type == 'date') {
                if ($attribute->code == 'special_price_from') {
                    $data[$attribute->code] = $specialFrom;
                } elseif ($attribute->code == 'special_price_to') {
                    $data[$attribute->code] = $specialTo;
                } else {
                    $data[$attribute->code] = $date->toDateString();
                }
            } elseif ($attribute->code != 'tax_category_id' && ($attribute->type == 'select' || $attribute->type == 'multiselect')) {
                $options = $attribute->options;

                if ($attribute->type == 'select') {
                    if ($options->count()) {
                        $option = $options->first()->id;

                        $data[$attribute->code] = $option;
                    } else {
                        $data[$attribute->code] = "";
                    }
                } elseif ($attribute->type == 'multiselect') {
                    if ($options->count()) {
                        $option = $options->first()->id;

                        $optionArray = [];

                        array_push($optionArray, $option);

                        $data[$attribute->code] = $optionArray;
                    } else {
                        $data[$attribute->code] = "";
                    }
                } else {
                    $data[$attribute->code] = "";
                }
            } elseif ($attribute->code == 'checkbox') {
                $options = $attribute->options;

                if ($options->count()) {
                    $option = $options->first()->id;

                    $optionArray = [];

                    array_push($optionArray, $option);

                    $data[$attribute->code] = $optionArray;
                } else {
                    $data[$attribute->code] = "";
                }
            }
        }

        // special price has to be less than price and not less than cost
        // cost has to be less than price
        if($data['special_price'] && $data['price'] && $data['cost']) {
            if ($data['cost'] >= $data['price'] || $data['cost'] < ($data['price']*0.3)) {
                $data['cost'] = $data['price'] * 0.75;
            }
            $data['special_price'] = $data['price'] -  ($data['price'] - $data['cost']) * (rand(20,70)/100);
        }

        $channel = core()->getCurrentChannel();

        $data['locale'] = core()->getCurrentLocale()->code;

        $brand = Attribute::where(['code' => 'brand'])->first();
        $data['brand'] = AttributeOption::where(['attribute_id' => $brand->id])->first()->id ?? '';

        $data['channel'] = $channel->code;

        $data['channels'] = [
            0 => $channel->id,
        ];

        $inventorySource = $channel->inventory_sources[0];

        $data['inventories'] = [
            $inventorySource->id => 10,
        ];

        $data['categories'] = [
            0 => $channel->root_category->id,
        ];

        $updated = $this->productRepository->update($data, $product->id);

        return $updated;
    }

    /**
     * @param \Webkul\Attribute\Models\AttributeFamily $attributeFamily
     * @return \Illuminate\Support\Collection
     */
    public function getFamilyAttributes($attributeFamily)
    {
        $attributes = collect();
        $attributeGroups = $attributeFamily->attribute_groups;

        foreach ($attributeGroups as $attributeGroup) {
            foreach ($attributeGroup->custom_attributes as $customAttribute) {
                $attributes->push($customAttribute);
            }
        }

        return $attributes;
    }
}