import React from "react";
import {Handle, Position} from "reactflow";

export function WorkflowNode({ label, type='', children }) {
    return (
        <>
            <Handle type="target" position={Position.Left} style={{top: '20px'}} />
            <div>
                <div className={['workflow-node-label', type].join(' ')}>{label}</div>
            </div>
            <div className="workflow-contents workflow-space-y">
                {children}
            </div>
            <Handle type="source" position={Position.Right} style={{top: '20px'}} id="a" />
        </>
    );
}
