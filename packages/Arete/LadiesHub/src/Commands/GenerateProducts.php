<?php

namespace Arete\LadiesHub\Commands;

use Illuminate\Console\Command;
use Arete\LadiesHub\Helpers\GenerateFashionProduct;

class GenerateProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ladieshub:generate {value} {quantity}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate Products for Ladies Hub';

    /**
     * GenerateProduct instance
     */
    protected $generateProduct;

    /**
     * ProductRepository instance
     */
    protected $product;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(GenerateFashionProduct $generateProduct)
    {
        parent::__construct();

        $this->generateProduct = $generateProduct;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
       print_r($this->generateProduct->createLingerieAttributeFamily());
        return true;
        if (! is_string($this->argument('value')) || ! is_numeric($this->argument('quantity'))) {
            $this->info('Illegal parameters or value of parameters are passed');
        } else {
            
            if (strtolower($this->argument('value')) == 'product' || strtolower($this->argument('value')) == 'products') {
                $quantity = (int)$this->argument('quantity');

                // @see https://laravel.com/docs/6.x/artisan#writing-output
                // @see https://symfony.com/doc/current/components/console/helpers/progressbar.html
                $bar = $this->output->createProgressBar($quantity);

                $this->line("Generating $quantity {$this->argument('value')}.");

                $bar->start();

                $generatedProducts = 0;
                $this->generateProduct->generateDemoBrand();
                while ($quantity > 0) {
                    try {
                        $result = $this->generateProduct->create();
                        $generatedProducts++;
                        $bar->advance();
                    } catch (\Exception $e) {
                        report($e);
                        continue;
                    }

                    $quantity--;
                }


                if ($result) {
                    $bar->finish();
                    $this->info("\n$generatedProducts Product(s) created successfully.");
                } else {
                    $this->info('Product(s) cannot be created successfully.');
                }
            } else {
                $this->line('Sorry, this generate option is invalid.');
            }

            print_r($this->generateProduct->createAttributes());
        }
    }
}
