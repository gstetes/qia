/** Controle que pode receber foco programaticamente (ex.: primeiro campo com erro após o envio). */
export interface Focusable {
    focus: () => void;
}
