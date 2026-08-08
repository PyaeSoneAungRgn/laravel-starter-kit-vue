<?php

namespace Modules\Demo\Enums;

enum ProductCategory: string
{
    case Electronics = 'electronics';
    case Clothing = 'clothing';
    case Accessories = 'accessories';
    case Food = 'food';
}
