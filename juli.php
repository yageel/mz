<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 2017/6/2
 * Time: 6:36
 */
$username = "13922806074";
echo substr($username,0,3)."****".substr($username,7);
die();

/*传入的参数为  纬度 纬度 经度 ASC升序由近至远 DESC 降序 由远到近 */
// 传入参数 纬度 40.0497810000 经度 116.3424590000
$sql = "
SELECT
*,
    ROUND(
        6378.138 * 2 * ASIN(
            SQRT(
                POW(
                    SIN(
                        (
                            40.0497810000 * PI() / 180 - lat * PI() / 180
                        ) / 2
                    ),
                    2
                ) + COS(40.0497810000 * PI() / 180) * COS(lat * PI() / 180) * POW(
                    SIN(
                        (
                            116.3424590000 * PI() / 180 - lon * PI() / 180
                        ) / 2
                    ),
                    2
                )
            )
        ) * 1000
    ) AS juli
FROM
    customer
ORDER BY
    juli ASC";