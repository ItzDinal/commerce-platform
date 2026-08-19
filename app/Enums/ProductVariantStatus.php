<?php

namespace App\Enums;

enum ProductVariantStatus: string
{
    case DRAFT = 'draft';
    case ACTIVE = 'active';
    case ARCHIVED = 'archived';
}