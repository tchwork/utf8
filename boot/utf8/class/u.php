<?php // vi: set fenc=utf-8 ts=4 sw=4 et:

/**/if (DEBUG)
/**/{
        class u extends Patchwork\Utf8
        {
            static function __constructStatic()
            {
                trigger_error("Using class `u' for class `Patchwork\\Utf8' without declaring the alias with `use Patchwork\\Utf8 as u;' is deprecated", E_USER_DEPRECATED);
            }
        }
/**/}
/**/else
/**/{
        class u extends Patchwork\Utf8
        {
        }
/**/}
