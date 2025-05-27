export default {
    "**/*.php*": [
        "vendor/bin/duster lint --using=phpstan",
        "vendor/bin/duster fix --using=rector,pint"
    ]
}
