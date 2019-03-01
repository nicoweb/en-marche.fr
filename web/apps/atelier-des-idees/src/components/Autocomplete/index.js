import React from 'react';
import PropTypes from 'prop-types';
import classNames from 'classnames';
import icn_20px_contributors from './../../img/icn_20px_contributors.svg';
import icn_20px_comments from './../../img/icn_20px_comments.svg';

function Autocomplete(props) {
    return (
        <div className="autocomplete">
            <div className="autocomplete__wrapper">
                <ul>
                    <li>
                        <div>
                            <p>{props.ideaName}</p>
                            <div>
                                <span>
                                    <img src={icn_20px_contributors} alt="Contributeurs" /> {props.ideaContributors}
                                </span>
                                <span>
                                    <img src={icn_20px_comments} alt="Commentaires" /> {props.ideaComments}
                                </span>
                            </div>
                            <div>
                                <span>X</span>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    );
}

Autocomplete.defaultProps = {};

Autocomplete.propTypes = {};

export default Autocomplete;
