<script>
    window.adminAppBasePath = function () {
        const path = window.location.pathname.replace(/\/$/, '');
        const markers = [
            '/admin/customers',
            '/admin/applications',
            '/admin/settlement-managements',
            '/admin/flow-managements',
            '/master/data',
            '/applications/create',
        ];

        for (const marker of markers) {
            if (path === marker || path.endsWith(marker)) {
                return path.slice(0, path.length - marker.length);
            }
        }

        if (/\/applications\/complete\/\d+$/.test(path)) {
            return path.replace(/\/applications\/complete\/\d+$/, '');
        }

        return '';
    };

    window.adminApiUrl = function (path) {
        const normalizedPath = path.startsWith('/') ? path : `/${path}`;

        return window.adminAppBasePath() + normalizedPath;
    };
</script>
