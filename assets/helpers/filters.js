// Фильтры для полей с datatype = string
const stringFilters = [
  {
    iconName: 'font',
    label: 'Текст содержит..',
    value: '',
    type: 'text_contains',
  },
  {
    iconName: 'font',
    label: 'Текст не содержит..',
    value: '',
    type: 'text_not_contains',
  },
  {
    iconName: 'font',
    label: 'Текст начинается с..',
    value: '',
    type: 'text_start',
  },
  {
    iconName: 'font',
    label: 'Текст заканчивается на..',
    value: '',
    type: 'text_end',
  },
  {
    iconName: 'font',
    label: 'Текст в точности..',
    value: '',
    type: 'text_accuracy',
  },
];

// Фильтры для полей с datatype = number
const numberFilters = [
  {
    iconName: 'equals',
    label: 'Равно..',
    value: '',
    type: 'number_equal',
  },
  {
    iconName: 'not-equal',
    label: 'Не равно..',
    value: '',
    type: 'number_not_equal',
  },
  {
    iconName: 'less-than',
    label: 'Строгое неравенство..',
    valueFrom: '',
    valueTo: '',
    type: 'number_inequality',
    isStrict: true,
  },
  {
    iconName: 'less-than-equal',
    label: 'Нестрогое неравенство..',
    valueFrom: '',
    valueTo: '',
    type: 'number_inequality',
    isStrict: false,
  },
];

// Фильтры для полей с datatype = date
const dateFilters = [
  {
    iconName: 'calendar-day',
    label: 'Дата..',
    value: null,
    type: 'date_day',
  },
  {
    iconName: 'calendar-day',
    label: 'Дата до..',
    value: null,
    type: 'date_before',
  },
  {
    iconName: 'calendar-day',
    label: 'Дата после..',
    value: null,
    type: 'date_after',
  },
];

export const filtersList = {
  string: stringFilters,
  number: numberFilters,
  date: dateFilters,
};
