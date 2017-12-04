
// Объект для работы со слайдером:
var slider = {
    
    // Свойства объекта:
        // Массив с адресами изображений:
        slides:slidesArray,

        // Индекс текущего изображения:
        index:0,

    // Методы объекта:
        // Установка текущего изображения:
        set: function(image) {
            document.getElementById("slide").setAttribute("src", image);
        },

        // Инициализация слайдера (установка изображения с нулевым индексом):
        init: function() {
            this.set(this.slides[this.index]);
        },

        // Уменьшение индекса:
        left: function() {
            this.index--;
            if (this.index < 0) { this.index = this.slides.length-1; }
            this.set(this.slides[this.index]);
        },

        // Увеличение индекса:
        right: function() {
            this.index++;
            if (this.index == this.slides.length) { this.index = 0; }
            this.set(this.slides[this.index]);
        }
};

// После загрузки документа:
window.onload = function() {
    
    // Запуск слайдера с нулевым индексом:
    slider.init();

    // Периодическое увеличение индекса через интервал 5 секунд:
    setInterval(function() {
        slider.right();
    },5000);
};
