$( document ).ready(function() {
    getStats();

});

function getStats() {
    $.get('https://twitchtokengenerator.com/stats/api.php', function(resp) {
        var countries = resp.country_names;
        var country_results = resp.country_results;

        var scopes = resp.scopes;
        scopes.unshift("no_scope_recorded");

        var data = resp.data;

        var colors1 = [];
        var colors2 = [];
        for(var i = 0; i < countries.length; i++) {
            colors1.push(getRandomColor());
            colors2.push(getRandomColor());
        }


        new Chart(document.getElementById("scope-chart"), {
            type: 'horizontalBar',
            data: {
                labels: scopes,
                datasets: [
                    {
                        label: "Token Generations",
                        backgroundColor: colors1,
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

        new Chart(document.getElementById("region-chart"), {
            type: 'horizontalBar',
            data: {
                labels: countries,
                datasets: [
                    {
                        label: "Token Generations",
                        backgroundColor: colors2,
                        data: country_results
                    }
                ]
            },
            options: {
                legend: { display: false },
                title: {
                    display: true,
                    text: 'TwitchTokenGenerator.com Regions'
                },
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero: true
                        }
                    }],
                    xAxes: [{
                        ticks: {
                            beginAtZero: true
                        }
                    }]
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