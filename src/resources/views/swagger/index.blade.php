<!-- HTML for static distribution bundle build -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Swagger UI Administracion</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('/css/swagger-ui.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('/css/index.css') }}" />
    <link rel="icon" type="image/png" href="{{ asset('/icons/favicon-32x32.png') }}" sizes="32x32" />
    <link rel="icon" type="image/png" href="{{ asset('/icons/favicon-16x16.png') }}" sizes="16x16" />
</head>

<body>
    <div id="swagger-ui">
    </div>
    <script src="{{ asset('/js/swagger-ui-bundle.js') }}" charset="UTF-8"></script>
    <script src="{{ asset('/js/swagger-ui-standalone-preset.js') }}" charset="UTF-8"></script>

    <script>
        const url = "{{ url('/') }}"

        // This is a plugin for the SwaggerUI
        const CaseInsensitiveFilterPlugin = function(system) {
            return {
                fn: {
                    opsFilter: (taggedOps, phrase) => {
                        return taggedOps.filter((tagObj, tag) => {

                            path = tagObj.get("operations")['_tail']['array'][0]['_root']['entries'][0][1];

                            return (tag.toLowerCase().includes(phrase.toLowerCase()) || path.toLowerCase()
                                .includes(phrase.toLowerCase()))
                        });
                    }
                }
            }
        };
        window.onload = function() {
            //<editor-fold desc="Changeable Configuration Block">

            // the following lines will be replaced by docker/configurator, when it runs in a docker-container
            window.ui = SwaggerUIBundle({
                url: `${url}/swagger.json`,
                dom_id: '#swagger-ui',
                deepLinking: false,
                filter: true,
                presets: [
                    SwaggerUIBundle.presets.apis,
                    SwaggerUIStandalonePreset
                ],
                plugins: [
                    SwaggerUIBundle.plugins.DownloadUrl,
                    CaseInsensitiveFilterPlugin
                ],

                layout: "StandaloneLayout"
            });

            //</editor-fold>
        };
    </script>
</body>

</html>
