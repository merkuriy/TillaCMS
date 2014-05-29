<?php

/*
 *	класс Structure с набором системных методов
 */
class modules_structure_model {

    static function getChild ($parentId) {

        return sys::sql("
            SELECT sect1.`id` , sect1.`title`, sect1.`pos`, COUNT(sect2.`parent_id`) countChild
            FROM
                (
                    SELECT sect.`id` , sect.`title`, sect.`pos`
                    FROM
                        `prefix_Sections` sect
                    WHERE sect.`parent_id` = '$parentId'
                ) sect1
            LEFT JOIN
                `prefix_Sections` sect2
            ON sect1.`id` = sect2.`parent_id`
            GROUP BY sect1.`id`
            ORDER BY sect1.`pos`, sect1.`id`
        ", 1);
    }
}