import React, {useCallback, useMemo} from 'react';
import ReactFlow, {
    MiniMap,
    Controls,
    Background,
    useNodesState,
    useEdgesState,
    addEdge,
} from 'reactflow';
import 'reactflow/dist/base.css';
import './App.css';
import { Html as HtmlFetcher} from "../src/fetchers/Html";
import { Map as MapMutation} from "../src/mutations/Map";

const initialNodes = [
    { id: '1', position: { x: 50, y: 50 }, type: 'markhuot.workflow.fetchers.Html', data: { label: 'Fetch Html' } },
    { id: '2', position: { x: 450, y: 50 }, type: 'markhuot.workflow.mutations.Map', data: { label: 'Map Data' } },
    { id: '3', position: { x: 850, y: 50 }, data: { label: 'Store Data' } },
];

const initialEdges = [
    { id: 'e1-2', source: '1', target: '2' },
    { id: 'e1-3', source: '2', target: '3' },
];

function App() {
    const nodeTypes = useMemo(() => ({
        'markhuot.workflow.fetchers.Html': HtmlFetcher,
        'markhuot.workflow.mutations.Map': MapMutation,
    }), []);

    const [nodes, setNodes, onNodesChange] = useNodesState(initialNodes);
    const [edges, setEdges, onEdgesChange] = useEdgesState(initialEdges);

    const onConnect = useCallback((params) => setEdges((eds) => addEdge(params, eds)), [setEdges]);

    return (
        <div className="w-screen h-screen">
            <ReactFlow
                nodes={nodes}
                nodeTypes={nodeTypes}
                edges={edges}
                onNodesChange={onNodesChange}
                onEdgesChange={onEdgesChange}
                onConnect={onConnect}
            >
                <MiniMap />
                <Controls />
                <Background />
            </ReactFlow>
        </div>
    );
}

export default App
