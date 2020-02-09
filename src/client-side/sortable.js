(function () {

    var attachSortable = function(container) {
        if (typeof $.fn.sortable === 'undefined') {
            return;
        }

        return container.find('[data-image-manager-sortable-container]').sortable({
            items: '[data-image-manager-sortable-item]',
            update: function(event, ui) {
                var item, request, sortedId, previousId, nextId, data;

                item = ui.item;
                request = item.closest('[data-image-manager-sortable-container]').data('image-manager-sortable-container');

                sortedId = item.data('image-manager-sortable-item');
                previousId = item.prev().length ? item.prev().data('image-manager-sortable-item') : null;
                nextId = item.next().length ? item.next().data('image-manager-sortable-item') : null;

                data = {};
                data[request.parameters.sorted_id] = sortedId;

                if (null !== previousId) {
                    data[request.parameters.previous_id] = previousId;
                }
                if (null !== nextId) {
                    data[request.parameters.next_id] = nextId;
                }

                return $.nette.ajax({
                    type: 'GET',
                    url: request.endpoint,
                    data: data,
                    off: ['unique']
                });
            }
        });
    };

    $(function() {
        return attachSortable($(document));
    });

    $.nette.ext('plugin-image-manager-sortable', {
        init: function () {
            this.ext('snippets', true).after(function (el) {
                attachSortable(el);
            });
        }
    });

})();
