import React from 'react';
import PropTypes from 'prop-types';
import classNames from 'classnames';
import icn_20px_contributors from './../../img/icn_20px_contributors.svg';
import icn_20px_comments from './../../img/icn_20px_comments.svg';
import { Link } from 'react-router-dom';

function Autocomplete(props) {
    return (
        <div className="autocomplete">
            <div className="autocomplete__wrapper">
                <ul>
                    {props.values &&
						props.values.items.map(items => (
						    <Link
						        to={`/atelier-des-idees/proposition/${items.uuid}`}
						        className="idea-card__link"
						        key={items.uuid}
						    >
						        <li>
						            <div>
						                <p>{items.name}</p>
						            </div>
						            <div>
						                <span>
						                    <img src={icn_20px_contributors} alt="Contributeurs" />{' '}
						                    {items.contributors_count}
						                </span>
						                <span>
						                    <img src={icn_20px_comments} alt="Commentaires" /> {items.comments_count}
						                </span>
						            </div>
						            <div>
						                <span>X</span>
						            </div>
						        </li>
						    </Link>
						))}
                </ul>
            </div>
        </div>
    );
}

Autocomplete.defaultProps = {};

Autocomplete.propTypes = {};

export default Autocomplete;
