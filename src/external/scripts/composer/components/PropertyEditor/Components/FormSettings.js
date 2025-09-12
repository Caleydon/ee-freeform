import PropTypes from "prop-types";
import React from "react";
import * as FieldTypes from "../../../constants/FieldTypes";

export const FormSettings = ({ hash, integrationCount, editForm, editAdminNotifications, editIntegrations }) => (
  <div className="composer-form-settings">
    <a onClick={editForm}
       className={"button button--secondary form-settings" + (hash === FieldTypes.FORM ? " active" : "")}
       data-icon="settings"
       title="Form Settings"
    >
        Settings
    </a>

    <a onClick={editAdminNotifications}
       className={"button button--secondary notification-settings" + (hash === FieldTypes.ADMIN_NOTIFICATIONS ? " active" : "")}
       data-icon="mail"
       title="Admin Notifications"
    >
        Notifications
    </a>

    {integrationCount ?
      (
        <a onClick={editIntegrations}
           className={"button button--secondary crm-settings" + (hash === FieldTypes.INTEGRATION ? " active" : "")}
           data-icon="crm"
           title="CRM"
        >
            CRM
        </a>
      )
      : ""}
  </div>
);

FormSettings.propTypes = {
  editForm: PropTypes.func.isRequired,
  editIntegrations: PropTypes.func.isRequired,
  editAdminNotifications: PropTypes.func.isRequired,
  hash: PropTypes.string.isRequired,
  integrationCount: PropTypes.number.isRequired,
};

export default FormSettings;
