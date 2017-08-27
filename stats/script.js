$( document ).ready(function() {
    getStats();

});

function getStats() {
    $.get('https://twitchtokengenerator.com/stats/api.php', function(resp) {
        var scopes = resp.scopes;
        scopes.unshift("no_scope_recorded");

        var data = resp.data;

        var colors = [];
        for(var i = 0; i < 19; i++)
            colors.push(getRandomColor());

        new Chart(document.getElementById("scope-chart"), {
            type: 'horizontalBar',
            data: {
                labels: scopes,
                datasets: [
                    {
                        label: "Token Generations",
                        backgroundColor: colors,
                        data: data
                    }
                ]
            },
            options: {
                legend: { display: false },
                title: {
                    display: true,
                    text: 'TwitchTokenGenerator.com Scope Usage'
                }
            }
        });
    });
}

function getRandomColor() {
    var letters = '0123456789ABCDEF'.split('');
    var color = '#';
    for (var i = 0; i < 6; i++ ) {
        color += letters[Math.floor(Math.random() * 16)];
    }
    return color;
}