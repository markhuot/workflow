import React from "react";

export function WorkflowInput({ label, type='text', placeholder='' }) {
    return (
        <p>
            <label>
                <span className="workflow-label">{label}</span>
                {type === 'textarea' ? (
                    <textarea className="workflow-input" placeholder={placeholder}></textarea>
                ) : (
                    <input className="workflow-input" type="text" placeholder={placeholder} />
                )}
            </label>
        </p>
    );
}
