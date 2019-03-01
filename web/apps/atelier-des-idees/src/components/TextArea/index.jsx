import React from 'react';
import PropTypes from 'prop-types';
import classNames from 'classnames';
import Autocomplete from '../Autocomplete';

function TextArea(props) {
    return (
        <React.Fragment>
            <div className="text-area">
                <div className="text-area__input-wrapper">
                    <textarea
                        className={classNames('text-area__input', {
                            'text-area__input--error': props.error,
                        })}
                        disabled={props.disabled}
                        id={props.id}
                        maxLength={props.maxLength}
                        minLength={props.minLength}
                        name={props.name}
                        onChange={(e) => {
                            const { value } = e.target;
                            if (!props.maxLength || (props.maxLength && value.length <= props.maxLength)) {
                                props.onChange(e.target.value);
                            }
                        }}
                        placeholder={props.placeholder}
                        value={props.value}
                        autoFocus={props.autofocus}
                        haveAutoComplete={props.haveAutoComplete}
                    >
                        {props.value}
                    </textarea>
                    {props.maxLength && (
                        <div className="text-area__counter">{`${props.value.length}/${props.maxLength}`}</div>
                    )}
                </div>
                {props.error && <p className="text-area__error">{props.error}</p>}
            </div>
            {props.haveAutoComplete && props.minLength <= props.value.length && <Autocomplete />}
        </React.Fragment>
    );
}

TextArea.defaultProps = {
    id: '',
    disabled: false,
    maxLength: undefined,
    minLength: undefined,
    placeholder: '',
    autofocus: false,
    value: '',
    name: '',
    error: '',
    haveAutoComplete: false,
};

TextArea.propTypes = {
    id: PropTypes.string,
    maxLength: PropTypes.number,
    minLength: PropTypes.number,
    name: PropTypes.string,
    onChange: PropTypes.func.isRequired,
    placeholder: PropTypes.string,
    autofocus: PropTypes.bool,
    value: PropTypes.string,
    error: PropTypes.string,
    haveAutoComplete: PropTypes.bool,
};

export default TextArea;
