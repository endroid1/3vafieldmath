<?php
class field3vafieldmath extends cmsFormField {

    public $sql = 'float NULL DEFAULT NULL';
    public $filter_type = 'int';
    public $is_virtual = false;
    public $allow_index = false;
    public $has_options = true;

    protected $use_language = true;

    public function __construct($name, $options = false) {
        parent::__construct($name, $options);
        $this->title = $this->title ?: LANG_FIELD3VAFIELDMATH_TITLE;
    }

    public function afterAdd($content, $item, $model) {
        $result = $this->afterParse(null, $item);
        if ($item[$this->name] != $result) {
            $model->updateContent($content['name'], $item['id'], [
                $this->name => $result
            ]);
        }
        return $result;
    }

    public function afterUpdate($content, $item, $model) {
        $result = $this->afterParse(null, $item);
        if ($item[$this->name] != $result) {
            $model->updateContent($content['name'], $item['id'], [
                $this->name => $result
            ]);
        }
        return $result;
    }

    public function getOptions(): array {
        if (!$this->subject_name) {
            return [];
        }

        $operationItems = [
            '' => LANG_FIELD3VAFIELDMATH_SELECT_OPERATION,
            'add' => LANG_FIELD3VAFIELDMATH_OPERATION_ADD,
            'subtract' => LANG_FIELD3VAFIELDMATH_OPERATION_SUBTRACT,
            'multiply' => LANG_FIELD3VAFIELDMATH_OPERATION_MULTIPLY,
            'divide' => LANG_FIELD3VAFIELDMATH_OPERATION_DIVIDE,
            'power' => LANG_FIELD3VAFIELDMATH_OPERATION_POWER,
            'percentage' => LANG_FIELD3VAFIELDMATH_OPERATION_PERCENTAGE,
            'percentage_of' => LANG_FIELD3VAFIELDMATH_OPERATION_PERCENTAGE_OF,
            'add_percentage' => LANG_FIELD3VAFIELDMATH_OPERATION_ADD_PERCENTAGE,
            'subtract_percentage' => LANG_FIELD3VAFIELDMATH_OPERATION_SUBTRACT_PERCENTAGE,
            'max' => LANG_FIELD3VAFIELDMATH_OPERATION_MAX,
            'min' => LANG_FIELD3VAFIELDMATH_OPERATION_MIN
        ];

        $fieldGenerator = function() {
            return ['' => LANG_FIELD3VAFIELDMATH_SELECT_FIELD] + $this->getAvailableFormFields();
        };

        return [
            new fieldList('math1', [
                'title' => LANG_FIELD3VAFIELDMATH_OPT_MATH1,
                'hint' => LANG_FIELD3VAFIELDMATH_OPT_MATH1_HINT,
                'rules' => [['required']],
                'generator' => $fieldGenerator
            ]),
            new fieldList('operation1', [
                'title' => LANG_FIELD3VAFIELDMATH_OPT_OPERATION1,
                'hint' => LANG_FIELD3VAFIELDMATH_OPT_OPERATION1_HINT,
                'rules' => [['required']],
                'items' => $operationItems
            ]),
            new fieldList('math2', [
                'title' => LANG_FIELD3VAFIELDMATH_OPT_MATH2,
                'hint' => LANG_FIELD3VAFIELDMATH_OPT_MATH2_HINT,
                'rules' => [['required']],
                'generator' => $fieldGenerator
            ]),
            new fieldList('operation2', [
                'title' => LANG_FIELD3VAFIELDMATH_OPT_OPERATION2,
                'hint' => LANG_FIELD3VAFIELDMATH_OPT_OPERATION2_HINT,
                'items' => $operationItems
            ]),
            new fieldList('math3', [
                'title' => LANG_FIELD3VAFIELDMATH_OPT_MATH3,
                'hint' => LANG_FIELD3VAFIELDMATH_OPT_MATH3_HINT,
                'generator' => $fieldGenerator
            ]),
            new fieldList('operation3', [
                'title' => LANG_FIELD3VAFIELDMATH_OPT_OPERATION3,
                'hint' => LANG_FIELD3VAFIELDMATH_OPT_OPERATION3_HINT,
                'items' => $operationItems
            ]),
            new fieldList('math4', [
                'title' => LANG_FIELD3VAFIELDMATH_OPT_MATH4,
                'hint' => LANG_FIELD3VAFIELDMATH_OPT_MATH4_HINT,
                'generator' => $fieldGenerator
            ]),
            new fieldList('operation4', [
                'title' => LANG_FIELD3VAFIELDMATH_OPT_OPERATION4,
                'hint' => LANG_FIELD3VAFIELDMATH_OPT_OPERATION4_HINT,
                'items' => $operationItems
            ]),
            new fieldList('math5', [
                'title' => LANG_FIELD3VAFIELDMATH_OPT_MATH5,
                'hint' => LANG_FIELD3VAFIELDMATH_OPT_MATH5_HINT,
                'generator' => $fieldGenerator
            ]),
            new fieldCheckbox('show_formula', [
                'title' => LANG_FIELD3VAFIELDMATH_OPT_SHOW_FORMULA,
                'hint' => LANG_FIELD3VAFIELDMATH_OPT_SHOW_FORMULA_HINT,
                'default' => false
            ])
        ];
    }

    private function getAvailableFormFields(): array {
        $content_model = cmsCore::getModel('content');

        if (!$content_model->getContentTypeByName($this->subject_name)) {
            $content_model->setTablePrefix('');
        }

        $fields = $content_model->orderBy('ordering', 'asc')
            ->getContentFields($this->subject_name, false, false);

        $fields_types = [];

        foreach ($fields as $field) {
            if (in_array($field['type'], ['number', 'string', 'list', '3vafieldmath', 'checkbox', 'radio'])) {
                $fields_types[$field['name']] = $field['title'];
            }
        }

        return $fields_types;
    }

    private function calculate(float $value1, float $value2, string $operation): float {
        switch ($operation) {
            case 'add':
                return $value1 + $value2;
            case 'subtract':
                return $value1 - $value2;
            case 'multiply':
                return $value1 * $value2;
            case 'divide':
                return $value2 == 0 ? 0 : $value1 / $value2;
            case 'power':
                return pow($value1, $value2);
            case 'percentage':
                return ($value1 / 100) * $value2;
            case 'percentage_of':
                return $value2 == 0 ? 0 : ($value1 / $value2) * 100;
            case 'add_percentage':
                return $value1 + ($value1 * $value2 / 100);
            case 'subtract_percentage':
                return $value1 - ($value1 * $value2 / 100);
            case 'max':
                return max($value1, $value2);
            case 'min':
                return min($value1, $value2);
            default:
                return $value1;
        }
    }

    private function getOperationSymbol(string $operation): string {
        $symbols = [
            'add' => '+',
            'subtract' => '−',
            'multiply' => '×',
            'divide' => '÷',
            'power' => '^',
            'percentage' => '% от',
            'percentage_of' => 'от',
            'add_percentage' => '+%',
            'subtract_percentage' => '-%',
            'max' => 'max',
            'min' => 'min'
        ];
        return $symbols[$operation] ?? $operation;
    }

    private function getFormulaPart(float $value1, float $value2, string $operation, float $result): string {
        switch ($operation) {
            case 'percentage':
                return sprintf(LANG_FIELD3VAFIELDMATH_FORMULA_PERCENTAGE, $value1, $value2, $result);
            case 'percentage_of':
                return sprintf(LANG_FIELD3VAFIELDMATH_FORMULA_PERCENTAGE_OF, $value1, $value2, $result);
            case 'add_percentage':
                return sprintf(LANG_FIELD3VAFIELDMATH_FORMULA_ADD_PERCENTAGE, $value1, $value2, $result);
            case 'subtract_percentage':
                return sprintf(LANG_FIELD3VAFIELDMATH_FORMULA_SUBTRACT_PERCENTAGE, $value1, $value2, $result);
            case 'max':
                return sprintf(LANG_FIELD3VAFIELDMATH_FORMULA_MAX, $value1.', '.$value2, $result);
            case 'min':
                return sprintf(LANG_FIELD3VAFIELDMATH_FORMULA_MIN, $value1.', '.$value2, $result);
            default:
                return sprintf(LANG_FIELD3VAFIELDMATH_FORMULA_DEFAULT, $value1, $this->getOperationSymbol($operation), $value2, $result);
        }
    }

    private function getSimpleFormulaPart(float $value, string $operation): string {
        $symbol = $this->getOperationSymbol($operation);
        
        return in_array($operation, ['max', 'min']) 
            ? ' ' . $symbol . '(' . $value . ')'
            : ' ' . $symbol . ' ' . $value;
    }

    public function getFilterInput($value): string {
        return '';
    }

    private function getCalculationData(): array {
        return [
            'math_fields' => [
                $this->getOption('math1'),
                $this->getOption('math2'),
                $this->getOption('math3'),
                $this->getOption('math4'),
                $this->getOption('math5')
            ],
            'operations' => [
                $this->getOption('operation1'),
                $this->getOption('operation2'),
                $this->getOption('operation3'),
                $this->getOption('operation4')
            ],
            'show_formula' => $this->getOption('show_formula', false)
        ];
    }

    private function validateCalculationData(array $data): bool {
        return !empty($data['math_fields'][0]) && 
               !empty($data['math_fields'][1]) && 
               !empty($data['operations'][0]);
    }

    private function extractValues(array $math_fields, $item): array {
        $values = [];
        foreach ($math_fields as $index => $field) {
            if (!empty($field) && isset($item[$field]) && $item[$field] !== '' && $item[$field] !== null) {
                $values[$index] = (float)$item[$field];
            }
        }
        return $values;
    }

    private function performCalculation(array $values, array $math_fields, array $operations, bool $show_formula = false): array {
        if (empty($values)) {
            return [
                'result' => 0,
                'formatted_result' => 0,
                'priority_class' => 'priority-low',
                'formula_parts' => [],
                'simple_formula' => '0'
            ];
        }

        $result = $values[0] ?? 0;
        $formula_parts = [];
        $simple_formula = (string)($values[0] ?? 0);

        for ($i = 1; $i < count($math_fields); $i++) {
            if (empty($math_fields[$i]) || empty($operations[$i-1]) || !isset($values[$i])) {
                continue;
            }

            $value2 = $values[$i] ?? 0;
            if ($result === null || $value2 === null) {
                continue;
            }

            $previous_result = $result;
            $result = $this->calculate((float)$result, (float)$value2, $operations[$i-1]);

            if ($show_formula) {
                $formula_parts[] = $this->getFormulaPart($previous_result, $value2, $operations[$i-1], $result);
            }

            $simple_formula .= $this->getSimpleFormulaPart($value2, $operations[$i-1]);
        }

        $simple_formula .= ' = ' . $result;
        $formatted_result = ($result == (int)$result) ? (int)$result : round($result, 2);

        if ($result >= 12) {
            $priority_class = 'priority-high';
        } elseif ($result >= 6) {
            $priority_class = 'priority-medium';
        } else {
            $priority_class = 'priority-low';
        }

        return [
            'result' => $result,
            'formatted_result' => $formatted_result,
            'priority_class' => $priority_class,
            'formula_parts' => $formula_parts,
            'simple_formula' => $simple_formula
        ];
    }

    public function parse($value): string {
        if (!$this->item) {
            return '';
        }

        $data = $this->getCalculationData();
        
        if (!$this->validateCalculationData($data)) {
            return '<span class="priority-badge priority-low">'.LANG_FIELD3VAFIELDMATH_ERROR_REQUIRED_FIELDS.'</span>';
        }

        $values = $this->extractValues($data['math_fields'], $this->item);
        
        if (empty($values)) {
            return '';
        }

        $calculation = $this->performCalculation($values, $data['math_fields'], $data['operations'], $data['show_formula']);

        if ($data['show_formula']) {
            $formula_text = implode(" → ", $calculation['formula_parts']);
            $full_formula = $calculation['simple_formula'] . "\n\nДетали:\n" . $formula_text;
            return '<span class="priority-badge '.$calculation['priority_class'].'" title="'.htmlspecialchars($full_formula).'">'.$calculation['formatted_result'].'</span>';
        }

        return '<span class="priority-badge '.$calculation['priority_class'].'" title="'.$calculation['simple_formula'].'">'.$calculation['formatted_result'].'</span>';
    }

    public function parseTeaser($value): string {
        return $this->parse($value);
    }

    public function getInput($value): string {
        $data = $this->getCalculationData();
        
        if (!$this->validateCalculationData($data)) {
            return '<span style="color:red;">'.LANG_FIELD3VAFIELDMATH_ERROR_CONFIGURATION.'</span>';
        }

        $values = $this->extractValues($data['math_fields'], $this->item);
        
        if (empty($values)) {
            return '<span style="color:#999;">'.LANG_FIELD3VAFIELDMATH_NO_VALUES.'</span>';
        }

        $calculation = $this->performCalculation($values, $data['math_fields'], $data['operations']);

        $display_text = $data['show_formula'] 
            ? $calculation['simple_formula'] 
            : LANG_FIELD3VAFIELDMATH_CALCULATED . $calculation['formatted_result'];

        return html_input('hidden', $this->element_name, $calculation['result'], [
            'id' => $this->id
        ]) . '<span style="margin-left:10px; padding:5px; background:#f0f0f0; border-radius:3px;">'.$display_text.'</span>';
    }

    public function afterParse($value, $item): float {
        $data = $this->getCalculationData();
        
        if (!$this->validateCalculationData($data)) {
            return 0;
        }

        $values = $this->extractValues($data['math_fields'], $item);
        
        if (empty($values)) {
            return 0;
        }

        $result = $values[0] ?? 0;

        for ($i = 1; $i < count($data['math_fields']); $i++) {
            if (empty($data['math_fields'][$i]) || empty($data['operations'][$i-1]) || !isset($values[$i])) {
                continue;
            }
            
            $value2 = $values[$i] ?? 0;
            if ($result === null || $value2 === null) {
                continue;
            }
            
            $result = $this->calculate((float)$result, (float)$value2, $data['operations'][$i-1]);
        }

        return (float)$result;
    }
}