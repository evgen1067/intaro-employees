import { filtersList } from './filters';
import cloneDeep from 'lodash.clonedeep';

export function getEmployeeInformation(companies, departments, positions, competences, grades) {
  let employeeData = [
    {
      key: 'name',
      datatype: 'string',
      label: 'ФИО',
    },
    {
      key: 'gender',
      datatype: 'list',
      listItems: [
        {
          label: 'мужской',
          listValueId: 1,
        },
        {
          label: 'женский',
          listValueId: 2,
        },
        {
          label: 'не указано',
          listValueId: 3,
        },
      ],
      label: 'Пол',
    },
    {
      key: 'date_of_birth',
      datatype: 'date',
      label: 'Дата рождения',
    },
    {
      key: 'date_of_employment',
      datatype: 'date',
      label: 'Дата трудоустройства',
    },
    {
      key: 'status',
      datatype: 'list',
      label: 'Статус',
      listItems: [
        {
          label: 'работает',
          listValueId: 1,
        },
        {
          label: 'декрет',
          listValueId: 2,
        },
        {
          label: 'уволен',
          listValueId: 3,
        },
        {
          label: 'не указано',
          listValueId: 4,
        },
      ],
    },
    {
      key: 'company',
      datatype: 'list',
      label: 'Компания',
      listItems: companies,
    },
    {
      key: 'departments',
      datatype: 'list',
      label: 'Отделы',
      listItems: departments,
    },
    {
      key: 'position',
      datatype: 'list',
      label: 'Должность',
      listItems: positions,
    },
    {
      key: 'competence',
      datatype: 'list',
      label: 'Компетенция',
      listItems: competences,
    },
    {
      key: 'grade',
      datatype: 'list',
      label: 'Грейд',
      listItems: grades,
    },
    {
      key: 'workExperience',
      datatype: 'number',
      label: 'Стаж работы',
    },
    {
      key: 'date_of_dismissal',
      datatype: 'date',
      label: 'Дата увольнения',
    },
    {
      key: 'reason_of_dismissal',
      datatype: 'list',
      label: 'Причина увольнения',
      listItems: [
        {
          label: 'не пройден испытательный срок',
          listValueId: 1,
        },
        {
          label: 'проблемы с дисциплиной',
          listValueId: 2,
        },
        {
          label: 'не справлялся с поставленными задачами',
          listValueId: 3,
        },
        {
          label: 'сокращение',
          listValueId: 4,
        },
        {
          label: 'предложение о работе с высокой заработной платой',
          listValueId: 5,
        },
        {
          label: 'потерял ценность',
          listValueId: 6,
        },
        {
          label: 'не видит для себя профессионального развития',
          listValueId: 7,
        },
        {
          label: 'хочет сменить должность/направление',
          listValueId: 8,
        },
        {
          label: 'выгорание',
          listValueId: 9,
        },
        {
          label: 'релокация',
          listValueId: 10,
        },
      ],
    },
    {
      key: 'category_of_dismissal',
      datatype: 'list',
      label: 'Категория увольнения',
      listItems: [
        {
          label: 'добровольная',
          listValueId: 1,
        },
        {
          label: 'принудительная',
          listValueId: 2,
        },
        {
          label: 'нежелательная',
          listValueId: 3,
        },
      ],
    },
  ];

  let columns = [],
    filter = {};

  for (let i = 0; i < employeeData.length; i++) {
    columns.push({
      key: employeeData[i].key,
      label: employeeData[i].label,
      datatype: employeeData[i].datatype,
      tdAlign: 'center',
      thAlign: 'center',
    });
    filter[employeeData[i].key] =
      employeeData[i].datatype !== 'list'
        ? cloneDeep(filtersList[employeeData[i].datatype][0])
        : {
            iconName: '',
            label: '',
            value: '',
            type: 'list',
            listItems: employeeData[i].listItems,
          };
    if (employeeData[i].key === 'status') {
      filter[employeeData[i].key].value = 1;
    }
  }

  return {
    columns: columns,
    filter: filter,
    dataInfo: employeeData,
  };
}
