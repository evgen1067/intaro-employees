import cloneDeep from 'lodash.clonedeep';
import { filtersList } from './filters';

export function getEmployeeInformation() {
  let urgencies = [
    {
      label: 'низкая',
      listValueId: 1,
    },
    {
      label: 'средняя',
      listValueId: 2,
    },
    {
      label: 'высокая',
      listValueId: 3,
    },
    {
      label: 'приоритетная',
      listValueId: 4,
    },
    {
      label: 'холд',
      listValueId: 5,
    },
    {
      label: 'до лучшего',
      listValueId: 6,
    },
  ];

  let statuses = [
    {
      label: 'Найм закрыт',
      listValueId: 1,
    },
    {
      label: 'В процессе найма',
      listValueId: 2,
    },
  ];

  let hiringPlan = [
    {
      key: 'status',
      datatype: 'list',
      label: 'Статус позиции',
      listItems: statuses,
    },
    {
      key: '',
      datatype: 'string',
      label: 'Кто нужен',
    },
    {
      key: '',
      datatype: 'number',
      label: 'Количество',
    },
    {
      key: '',
      datatype: 'list',
      label: 'Срочность',
      listItems: urgencies,
    },
    {
      key: '',
      datatype: 'string',
      label: 'Руководитель',
    },
    {
      key: '',
      datatype: 'number',
      label: 'Сделанные офферы',
    },
    {
      key: '',
      datatype: 'number',
      label: 'Количество нанятых',
    },
    {
      key: '',
      datatype: 'string',
      label: 'Комментарий',
    },
  ];

  let columns = [],
    filter = {};

  for (let i = 0; i < hiringPlan.length; i++) {
    columns.push({
      key: hiringPlan[i].key,
      label: hiringPlan[i].label,
      datatype: hiringPlan[i].datatype,
      tdAlign: 'center',
      thAlign: 'center',
    });
    filter[hiringPlan[i].key] =
      hiringPlan[i].datatype !== 'list'
        ? cloneDeep(filtersList[hiringPlan[i].datatype][0])
        : {
            iconName: '',
            label: '',
            value: '',
            type: 'list',
            listItems: hiringPlan[i].listItems,
          };
  }

  return {
    columns: columns,
    filter: filter,
    dataInfo: hiringPlan,
  };
}
