import {
    animate,
    anticipate,
    backIn,
    backInOut,
    backOut,
    circIn,
    circInOut,
    circOut,
    cubicBezier,
    easeIn,
    easeInOut,
    easeOut,
    hover,
    inView,
    spring,
    stagger,
} from "motion";

// Extend the Window interface to include the motion property
declare global {
    interface Window {
        motion: {
            animate: typeof animate;
            hover: typeof hover;
            inView: typeof inView;
            easeIn: typeof easeIn;
            easeOut: typeof easeOut;
            easeInOut: typeof easeInOut;
            backOut: typeof backOut;
            backIn: typeof backIn;
            backInOut: typeof backInOut;
            circIn: typeof circIn;
            circOut: typeof circOut;
            circInOut: typeof circInOut;
            anticipate: typeof anticipate;
            spring: typeof spring;
            stagger: typeof stagger;
            cubicBezier: typeof cubicBezier;
        };
    }
}

// Motion
window.motion = {
    animate: animate,
    hover: hover,
    inView: inView,
    easeIn: easeIn,
    easeOut: easeOut,
    easeInOut: easeInOut,
    backOut: backOut,
    backIn: backIn,
    backInOut: backInOut,
    circIn: circIn,
    circOut: circOut,
    circInOut: circInOut,
    anticipate: anticipate,
    spring: spring,
    stagger: stagger,
    cubicBezier: cubicBezier,
};
