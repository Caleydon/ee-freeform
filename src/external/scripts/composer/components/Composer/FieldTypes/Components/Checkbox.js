/*
 * Freeform for ExpressionEngine
 *
 * @package       Solspace:Freeform
 * @author        Solspace, Inc.
 * @copyright     Copyright (c) 2008-2025, Solspace, Inc.
 * @link          https://docs.solspace.com/expressionengine/freeform/v3/
 * @license       https://docs.solspace.com/license-agreement/
 */

import PropTypes from "prop-types";
import React from "react";
import { CHECKBOX } from "../../../../constants/FieldTypes";
import HtmlInput from "../HtmlInput";

export default class Checkbox extends HtmlInput {
  static propTypes = {
    label: PropTypes.node.isRequired,
    properties: PropTypes.object.isRequired,
    isChecked: PropTypes.bool.isRequired,
    isRequired: PropTypes.bool,
  };

  getType() {
    return CHECKBOX;
  }

  render() {
    const { properties, label, isChecked, value, isRequired } = this.props;
    const { id, handle } = properties;
    const htmlFor = String(`${id}_${value}_${handle}`).toLowerCase().replace(/\s+/g, '-').replace(/[^a-z0-9-_]/g, '');
    const labelClass = ["composer-field-checkbox-single"];
    if (isRequired) {
      labelClass.push("composer-field-required");
    }

    return (
      <div>
        <label htmlFor={htmlFor} className={labelClass.join(" ")}>
          <input
            id={htmlFor}
            className="composer-ft-checkbox"
            type={this.getType()}
            value={value}
            readOnly={true}
            disabled={true}
            checked={isChecked}
            {...this.getCleanProperties()}
          />
          <span dangerouslySetInnerHTML={{ __html: label }} />
          {isRequired ? <span className="required" /> : ""}
          {this.props.children}
        </label>
      </div>
    );
  }
}
