import jq from 'jquery';

export default function({$element, url, onReady}) {
    $element.addClass('loading');
    jq.ajax({url, method: 'GET'}).then((content) => {
        const $content = jq(content);
        onReady($content);
        $element.replaceWith($content);
        $element.removeClass('loading');
    });
};