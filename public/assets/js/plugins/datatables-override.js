function initializeDataTable(tableSelector, footerColumnIndex, customColumnDefs) {
	return new DataTable(tableSelector, {
		"language": {
			"url": "/assets/js/fr-FR.json"
		},
		searching: true,
		fixedHeight: true,
		bLengthChange: false,
		paging: true,
		showNEntries: false,
		pageLength: 30,
		"columnDefs": customColumnDefs,
		"footerCallback": function (row, data, start, end, display) {
			if (footerColumnIndex >= 0) {
				let api = this.api();

				let intVal = function (i) {
					if (typeof i === 'string') {
						let cleaned = i.replace(' €', '')
							.replace(/ /g, '');

						if (cleaned.indexOf(',') > -1 && cleaned.indexOf('.') > -1) {
							return cleaned.replace(/\./g, '').replace(',', '.') * 1;
						} else if (cleaned.indexOf(',') > -1) {
							return cleaned.replace(',', '.') * 1;
						} else {
							return cleaned * 1;
						}
					}
					return typeof i === 'number' ? i : 0;
				};

				let total = api
					.column(footerColumnIndex)
					.data()
					.reduce(function (a, b) {
						return intVal(a) + intVal(b);
					}, 0);

				let pageTotal = api
					.column(footerColumnIndex, {page: 'current'})
					.data()
					.reduce(function (a, b) {
						return intVal(a) + intVal(b);
					}, 0);

				let formatNumber = (num) => num.toLocaleString('fr-FR', {
					minimumFractionDigits: 2,
					maximumFractionDigits: 2
				});

				api.column(footerColumnIndex).footer().innerHTML =
					formatNumber(pageTotal) + ' € <br/> ' + formatNumber(total) + ' €';
			}
		}
	});
}

function setColumnDefsAmount(columnIndex)
{
	return [
        {
            "targets": 0,
            "className": "truncate-column"
        },
		{
			"targets": columnIndex,
			"type": "num",
			"render": {
				"sort": function (data, type, row) {
					let cleaned = data.toString()
						.replace(' €', '')
						.replace(/ /g, '')
						.replace(',', '.');

					return parseFloat(cleaned);
				},
				"display": function (data, type, row) {
					let numberValue;
					if (typeof data === 'string') {
						numberValue = parseFloat(data.replace(' €', '').replace(/ /g, '').replace(',', '.'));
					} else {
						numberValue = data;
					}

					if (numberValue === 0){
						return '';
					} else {
						return numberValue.toLocaleString('fr-FR', {
							minimumFractionDigits: 2,
							maximumFractionDigits: 2
						}) + ' €';
					}
				}
			}
		}
	]
}

function datatableSearch(value){
	$("#datatable-search").val(value).keyup();
}
