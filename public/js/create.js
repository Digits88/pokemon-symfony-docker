function addPokemonToTeam() {
    $.getJSON("/pokemon/new", function (data) {
        let i;
        let pokemon = '<img src="' + data.sprite + '" class="card-img-top" alt="' + data.name + '">' + '<div class="card-body">' + '<h5 class="card-title">' + data.name + '</h5>' + '<h6 class="card-subtitle mb-2 text-muted">' + data.baseExperience + '</h6>' + '<p class="card-text">Abilities:';
        for (i = 0; i < data.abilities.length;) {
            pokemon += ' ' + data.abilities[i]['name'];
            i++;
            if (data.abilities.length !== i) {
                pokemon += ',';
            }
        }
        pokemon.substring(0, pokemon.length - 1);
        pokemon += '</p><p class="card-text">Types: ';
        for (i = 0; i < data.types.length;) {
            pokemon += ' ' + data.types[i]['name'];
            i++;
            if (data.types.length !== i) {
                pokemon += ',';
            }
        }
        pokemon.substring(0, pokemon.length - 1);
        pokemon += '</p></div>';
        const innerDiv = document
            .querySelector('#PokemonTeam')
            .getElementsByClassName('free')
        if (innerDiv.length <= 0) {
            alert("Error: A trainer cannot own more than 6 pokemon");
            return false;
        }
        innerDiv[0].innerHTML = '';
        console.log(innerDiv);
        innerDiv[0].innerHTML = pokemon;
        const HiddenPokemonList = document.getElementById("team_ajaxString");
        if (HiddenPokemonList.value === "") {
            HiddenPokemonList.value = JSON.stringify(data);
        } else {
            HiddenPokemonList.value = HiddenPokemonList.value + "," + JSON.stringify(data);
        }
        innerDiv[0].classList.remove("free");
    });
}

function validateForm() {
    const innerDiv = document
        .querySelector('#PokemonTeam')
        .getElementsByClassName('free')
    if (innerDiv.length >= 6) {
        alert("Error: Please add at least one Pokemon on your team. Pressing the button Gotta Catch 'Em All");
        return false;
    }
}



