import cloneDeep from 'lodash.clonedeep';
import { filtersList } from './filters';

export function getHiringInformation(hrManagers, currentId) {
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
      key: 'manager_name',
      datatype: 'list',
      label: 'HR менеджер',
      listItems: hrManagers,
    },
    {
      key: 'status',
      datatype: 'list',
      label: 'Статус позиции',
      listItems: statuses,
    },
    {
      key: 'position',
      datatype: 'string',
      label: 'Кто нужен',
    },
    {
      key: 'expected_count',
      datatype: 'number',
      label: 'Количество',
    },
    {
      key: 'urgency',
      datatype: 'list',
      label: 'Срочность',
      listItems: urgencies,
    },
    {
      key: 'director',
      datatype: 'string',
      label: 'Руководитель',
    },
    {
      key: 'offers_count',
      datatype: 'number',
      label: 'Сделанные офферы',
    },
    {
      key: 'employees_count',
      datatype: 'number',
      label: 'Количество нанятых',
    },
    {
      key: 'comment',
      datatype: 'string',
      label: 'Комментарий',
    },
  ];

  let columns = [],
    filter = {},
    defaultItem = {};

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
    defaultItem[hiringPlan[i].key] = hiringPlan[i].datatype === 'string' ? '' : 0;
    if (hiringPlan[i].key === 'status') {
      defaultItem[hiringPlan[i].key] = 2;
      filter[hiringPlan[i].key].value = 2;
    } else if (hiringPlan[i].key === 'urgency') {
      defaultItem[hiringPlan[i].key] = 1;
    } else if (hiringPlan[i].key === 'manager_name') {
      defaultItem[hiringPlan[i].key] = currentId;
      filter[hiringPlan[i].key].value = currentId;
    }
  }
  return {
    columns: columns,
    filter: filter,
    dataInfo: hiringPlan,
    defaultItem: defaultItem,
  };
}
