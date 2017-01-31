import jq from 'jquery';

const setLoading = function($modal) {
  $modal.find('.modal-dialog').addClass('loading');
};

const unsetLoading = function($modal) {
    $modal.find('.modal-dialog').removeClass('loading');
};

const configureModalForm = function ($modal, content, successCallback) {
    $modal.find('.modal-dialog').html(content);
    $modal.find('form').ajaxForm({
        beforeSubmit: function () {
            setLoading($modal);
        },
        success: function (json) {
            unsetLoading($modal);
            configureModalForm($modal, json.content, successCallback);
            if (json.status === 'success') {
                successCallback();
            }
        },
        error: function (json) {
            unsetLoading($modal);
            configureModalForm($modal, json.content, successCallback);
        },
    });
};

export default function($parent, {onSuccess}) {
    $parent.find('.x-modal-form').each((i, element) => {
        const $target = jq(element);
        if ($target.html().trim() == '') {
            $target.addClass('x-modal-form-editable-empty');
            $target.append('<i class="fa fa-pencil"></i>');
        }
    });

    $parent.find('.x-modal-form').on('click', (event) => {
        event.preventDefault();
        const $target = jq(event.delegateTarget);

        const $modal = jq(`
            <div class="modal fade">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-body">
                            Loading...
                        </div>
                    </div>
                </div>
            </div>
        `);
        jq('body').append($modal);
        $modal.modal();
        setLoading($modal);

        jq.ajax({url: $target.data('url'), method: 'GET'}).then(({content}) => {
            unsetLoading($modal);
            configureModalForm($modal, content, () => {
                onSuccess({$target});
            });
        });
    });
};