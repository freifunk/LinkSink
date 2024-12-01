import $ from 'jquery';
import 'bootstrap';
import './styles/links.css';
import '@selectize/selectize';

console.log("here we are");
document.addEventListener('DOMContentLoaded', function() {
    const selectElements = document.querySelectorAll('#link_category, #link_tags');
    selectElements.forEach(element => {
        $(element).selectize();
    });
});