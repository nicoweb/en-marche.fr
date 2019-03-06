import React, { Component } from 'react';
import PropTypes from 'prop-types';
import classNames from 'classnames';
import AutoComplete from '../AutoComplete';
import { keyPressed } from './../../helpers/navigation';
class TextArea extends Component {
	state = {
		value: '',
		autoCompleteIsOpen: false
	};

	componentDidMount() {
		document.getElementById(this.props.id).focus;
	}
	componentWillUnmount() {
		document.onkeydown = null;
	}

	render() {
		return (
			<React.Fragment>
				<div className="text-area">
					<div className="text-area__input-wrapper">
						<textarea
							className={classNames('text-area__input', {
								'text-area__input--error': this.props.error
							})}
							disabled={this.props.disabled}
							id={this.props.id}
							maxLength={this.props.maxLength}
							minLength={this.props.minLength}
							name={this.props.name}
							onChange={e => {
								const { value } = e.target;
								this.setState({
									value: e.target.value,
									autoCompleteIsOpen: true
								});

								if (
									!this.props.maxLength ||
									(this.props.maxLength && value.length <= this.props.maxLength)
								) {
									this.props.onChange(e.target.value);
								}
							}}
							placeholder={this.props.placeholder}
							value={this.props.value}
							autoFocus={this.props.autofocus}
							haveAutoComplete={this.props.haveAutoComplete}
							data-selectlist={this.props.dataSelectlist}
							data-prev={this.props.dataPrev}
							data-next={this.props.dataNext}
						>
							{this.props.value}
						</textarea>
						{this.props.maxLength && (
							<div className="text-area__counter">{`${this.props.value.length}/${
								this.props.maxLength
							}`}</div>
						)}
					</div>
					{this.props.haveAutoComplete && 1 <= this.props.value.length && this.state.autoCompleteIsOpen && (
						<AutoComplete options={this.props.autoCompleteValues} value={this.state.value} />
					)}
					{this.props.error && <p className="text-area__error">{this.props.error}</p>}
				</div>
			</React.Fragment>
		);
	}
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
	haveAutoComplete: false
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
	haveAutoComplete: PropTypes.bool
};

export default TextArea;
