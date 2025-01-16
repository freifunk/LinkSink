//import './bootstrap.js';
import $ from 'jquery';
import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap';
import {Tooltip} from 'bootstrap';
import 'bootstrap-icons/font/bootstrap-icons.min.css';
import '@fontsource/lato/index.min.css';
import '@fontsource/lato/700.css';
import '@fontsource/lato/900.css';
import '@selectize/selectize';
import '@selectize/selectize/dist/css/selectize.bootstrap5.css';
import './styles/links.css';

document.addEventListener('DOMContentLoaded', function () {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new Tooltip(tooltipTriggerEl);
    });
});

$("#link_tags").selectize({
    diacritics: true,
    valueField: 'name',
    labelField: 'name',
    searchField: 'name',
    create: true,
    load: function (query, callback) {
        if (!query.length) return callback();
        $.ajax({
            url: "/tags/query/",
            type: "GET",
            dataType: 'json',
            data: {
                q: query
            },
            error: function () {
                callback();
            },
            success: function (res) {
                console.log(res);
                callback(res);
            }
        });
    }
});



(() => {
    'use strict'

    // Fetch all the forms we want to apply custom Bootstrap validation styles to
    const forms = document.querySelectorAll('.needs-validation')

    // Loop over them and prevent submission
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault()
                event.stopPropagation()
            }

            form.classList.add('was-validated')
        }, false)
    })
})()