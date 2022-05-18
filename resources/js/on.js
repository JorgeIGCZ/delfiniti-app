//Using event-delegation
export const on = (eventName, selector, handler) => {
    document.addEventListener(
      eventName,
      event => {
        const elements = document.querySelectorAll(selector);
        const path = event.composedPath();
  
        path.forEach(node => {
          elements.forEach(elem => {
            if (node === elem) {
              handler.call(elem, event);
            }
          });
        });
      },
      true
    );
};  