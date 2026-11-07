<?php
/**
 * Rules: required, email, student_email, min:N, max:N, phone, password, confirmed,
 * integer, numeric, between:A,B, in:a,b, date, after:field, unique:table,Column, digits:N
 */
class Validator
{
    public static function make(array $data, array $rules): array
    {
        $errors = [];
        foreach ($rules as $field => $ruleString) {
            $value = $data[$field] ?? null;
            $value = is_string($value) ? trim($value) : $value;
            $rulesList = is_array($ruleString) ? $ruleString : explode('|', $ruleString);
            $nullable = in_array('nullable', $rulesList, true);

            foreach ($rulesList as $rule) {
                [$name, $param] = array_pad(explode(':', $rule, 2), 2, null);
                if ($name === 'nullable') {
                    continue;
                }
                if ($name !== 'required' && ($value === null || $value === '')) {
                    if ($nullable || !in_array('required', $rulesList, true)) {
                        continue;
                    }
                }
                $msg = self::check($name, $param, $value, $data, $field);
                if ($msg !== null) {
                    $errors[$field] = $msg;
                    break;
                }
            }
        }
        return $errors;
    }

    private static function check(string $name, ?string $param, mixed $value, array $data, string $field): ?string
    {
        $str = is_scalar($value) ? (string) $value : '';
        return match ($name) {
            'required'      => ($value === null || $str === '') ? __('v.required') : null,
            'email'         => filter_var($str, FILTER_VALIDATE_EMAIL) ? null : __('v.email'),
            'student_email' => in_array(substr(strrchr(strtolower($str), '@') ?: '', 1), student_domains(), true)
                                ? null : __('v.student_email', ['domain' => implode(', @', student_domains())]),
            'min'           => mb_strlen($str) < (int) $param ? __('v.min', ['n' => $param]) : null,
            'max'           => mb_strlen($str) > (int) $param ? __('v.max', ['n' => $param]) : null,
            'phone'         => preg_match('/^05[0-9]{8}$/', $str) ? null : __('v.phone'),
            'password'      => (mb_strlen($str) >= 8 && preg_match('/[A-Za-z]/', $str) && preg_match('/[0-9]/', $str))
                                ? null : __('v.password'),
            'confirmed'     => $str === (string) ($data[$field . '_confirmation'] ?? '') ? null : __('v.confirmed'),
            'integer'       => preg_match('/^-?\d+$/', $str) ? null : __('v.integer'),
            'numeric'       => is_numeric($str) ? null : __('v.numeric'),
            'between'       => (function () use ($param, $str) {
                [$a, $b] = explode(',', (string) $param);
                return (is_numeric($str) && $str >= $a && $str <= $b) ? null : __('v.between', ['a' => $a, 'b' => $b]);
            })(),
            'in'            => in_array($str, explode(',', (string) $param), true) ? null : __('v.in'),
            'date'          => (DateTime::createFromFormat('Y-m-d', $str) && DateTime::createFromFormat('Y-m-d', $str)->format('Y-m-d') === $str)
                                ? null : __('v.date'),
            'after'         => ($str > (string) ($data[$param] ?? '')) ? null : __('v.after'),
            'digits'        => preg_match('/^\d{' . (int) $param . '}$/', $str) ? null : __('v.digits', ['n' => $param]),
            'unique'        => (function () use ($param, $str) {
                [$table, $col] = explode(',', (string) $param);
                if (!preg_match('/^[a-z_]+$/', $table) || !preg_match('/^[A-Za-z]+$/', $col)) {
                    throw new InvalidArgumentException('Bad unique rule');
                }
                $exists = Database::value("SELECT 1 FROM `$table` WHERE `$col` = ? LIMIT 1", [$str]);
                return $exists ? __('v.unique') : null;
            })(),
            default         => throw new InvalidArgumentException("Unknown rule $name"),
        };
    }
}
