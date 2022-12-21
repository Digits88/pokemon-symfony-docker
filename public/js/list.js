const selectElement = document.querySelector('.selectpicker');

selectElement.addEventListener('change', (event) => {
    let selected = document.querySelectorAll('.selectpicker option:checked');
    const ListTypeIdToShow = Array.from(selected).map(el => el.value);
    document.querySelectorAll('[id^=team_id_]').forEach(element => {
        let visibleRow = true;
        let TypeIdsAssociateAtThisTeam = [];
        element.querySelectorAll('td ul li').forEach(ee => {
            TypeIdsAssociateAtThisTeam.push(parseInt(ee.dataset.type));
        });
        for (let i = 0; i < ListTypeIdToShow.length; i++) {
            let typeID = parseInt(ListTypeIdToShow[i]);
            if (TypeIdsAssociateAtThisTeam.includes(typeID)) {
                visibleRow = false;
                break;
            }
        }
        if (visibleRow) {
            element.style.display = 'none';
        } else {
            element.style.display = '';
        }
    });
});

$('select').selectpicker('selectAll');

