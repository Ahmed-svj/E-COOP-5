<?php
abstract class Model
{
    protected static string $table;
    protected static string $pk;

    public static function find(int $id): ?array
    {
        return Database::one('SELECT * FROM `' . static::$table . '` WHERE `' . static::$pk . '` = ? LIMIT 1', [$id]);
    }

    public static function all(string $orderBy = ''): array
    {
        $order = preg_match('/^[A-Za-z]+( (ASC|DESC))?$/', $orderBy) ? " ORDER BY $orderBy" : '';
        return Database::all('SELECT * FROM `' . static::$table . '`' . $order);
    }

    /** Insert a row from [Column => value]; returns the new id. */
    public static function insert(array $data): int
    {
        self::guardColumns(array_keys($data));
        $cols = '`' . implode('`,`', array_keys($data)) . '`';
        $marks = implode(',', array_fill(0, count($data), '?'));
        Database::query('INSERT INTO `' . static::$table . "` ($cols) VALUES ($marks)", array_values($data));
        return (int) Database::connect()->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        self::guardColumns(array_keys($data));
        $set = implode(', ', array_map(fn ($c) => "`$c` = ?", array_keys($data)));
        Database::query('UPDATE `' . static::$table . "` SET $set WHERE `" . static::$pk . '` = ?', [...array_values($data), $id]);
    }

    private static function guardColumns(array $cols): void
    {
        foreach ($cols as $c) {
            if (!preg_match('/^[A-Za-z]+$/', $c)) {
                throw new InvalidArgumentException("Bad column $c");
            }
        }
    }
}
