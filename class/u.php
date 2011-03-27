<?php // vi: set encoding=utf-8 expandtab shiftwidth=4:

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
